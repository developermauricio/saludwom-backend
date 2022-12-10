<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    public function applyCoupon(Request $request){

        $couponExists = Coupon::where('name', $request['coupon'])->first();

        if (!$couponExists){
            return response()->json([
                'success' => false,
                'message' => 'Cupón no existe',
                'response' => 'no_coupon_exist',

            ], 202);
        }

        if($couponExists->state !== '1'){
            return response()->json([
                'success' => false,
                'message' => 'Coupon inactivo',
                'response' => 'coupon_is_inactive',

            ], 202);
        }
        $date = Carbon::now();

        if ($couponExists->date_expiration < $date->format('Y-m-d H:i:s')){
            return response()->json([
                'success' => false,
                'message' => 'Coupon ha vencido',
                'response' => 'coupon_toexpired_inactive',

            ], 202);
        }

        $patient = Patient::where('user_id', auth()->id())->first();
        $couponUser = CouponUser::where('patient_id', $patient->id)->get();

        if (count($couponUser) === $couponExists->limit_use){
            return response()->json([
                'success' => false,
                'message' => 'Has llegado al limite de uso del cupón',
                'response' => 'coupon_limit_use',

            ], 202);
        }


        return response()->json([
            'success' => false,
            'message' => 'Cupón Disponible',
            'response' => 'coupon_available',
            'data' => $couponExists
        ], 200);

    }
}
