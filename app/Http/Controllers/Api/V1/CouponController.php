<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCoupon;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    public function getCoupons()
    {
        try {

            $coupons = Coupon::all();

            $coupons = $coupons->each(function ($coupon, $index) {
                $coupon->sequence_number = $index + 1;
            });

            return response()->json([
                'success' => true,
                'message' => 'Get coupons',
                'response' => 'get_coupons',
                'data' => $coupons
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET COUPONS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function editCoupon(RegisterCoupon $request, $couponId)
    {
        DB::beginTransaction();

        try {

            $exceptPlans = null;

            if ($request['exceptPlans']) {
                $exceptPlans = json_encode($request['exceptPlans']);
            }

            $coupon = Coupon::find($couponId);

            $coupon->update([
                'name' => $request['nameCoupon'],
                'description' => $request['description'],
                'discount' => $request['discount'],
                'create_user_id' => $request['createUserId'],
                'date_expiration' => Carbon::parse($request['dateExpiration'])->format('Y-m-d H:m:s'),
                'limit_use_per_user' => $request['limitUsePerUser'],
                'limit_use_per_coupon' => $request['limitUsePerCoupon'],
                'except_plans' => $exceptPlans,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Edit Coupon',
                'response' => 'edit_coupon'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR EDIT TREATMENT SPECIALTY.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function changeStatusCoupon($couponId)
    {

        DB::beginTransaction();

        try {

            $coupon = Coupon::findOrFail($couponId);
            Log::info($coupon);
            $state = $coupon->state == Coupon::ACTIVE ? Coupon::INACTIVE : Coupon::ACTIVE;
            $coupon->state = $state;
            $coupon->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $coupon,
                'message' => 'Change Status Coupon',
                'response' => 'change_status_coupon'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHANGE STATUS COUPON.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function deleteCoupon($couponId)
    {
        DB::beginTransaction();
        $success = false;
        $message = 'The specialty was not removed';

        try {

            $couponUsers = DB::table('coupon_users')
                ->where('coupon_id', $couponId)
                ->get();

            if (count($couponUsers) === 0) {
                $coupon = Coupon::find($couponId);
                $coupon->delete();

                $success = true;
                $message = 'Delete Coupon';
            }

            DB::commit();
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR DELETE COUPON.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function applyCoupon(Request $request)
    {
        DB::beginTransaction();

        try {

            $couponExists = Coupon::where('name', $request['coupon'])->first();

            if (!$couponExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no existe',
                    'response' => 'no_coupon_exist',
                    'plans' => $couponExists->except_plans

                ], 202);
            }

            if ($couponExists->state !== '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon inactivo',
                    'response' => 'coupon_is_inactive',
                    'plans' => $couponExists->except_plans

                ], 202);
            }
            $date = Carbon::now();

            if ($couponExists->date_expiration < $date->format('Y-m-d H:i:s')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon ha vencido',
                    'response' => 'coupon_to_expired_inactive',
                    'plans' => $couponExists->except_plans

                ], 202);
            }

            $patient = Patient::where('user_id', auth()->id())->first();
            $couponUser = CouponUser::where('patient_id', $patient->id)->get();

            if (count($couponUser) === $couponExists->limit_use_per_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Has llegado al limite de uso del cupón',
                    'response' => 'coupon_limit_use_per_user',
                    'plans' => $couponExists->except_plans

                ], 202);
            }

            $couponUser = CouponUser::where('coupon_id', $couponExists->id)->get();

            if (count($couponUser) === $couponExists->limit_use_per_coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ha llegado al limite de uso',
                    'response' => 'coupon_limit_use_per_coupon',
                    'plans' => $couponExists->except_plans

                ], 202);
            }

            return response()->json([
                'success' => false,
                'message' => 'Cupón Disponible',
                'response' => 'coupon_available',
                'plans' => $couponExists->except_plans,
                'coupon' => $couponExists
            ], 200);

        } catch (\Throwable $th) {

            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR APPLYCOUPON.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public
    function addCoupon(RegisterCoupon $request)
    {
        DB::beginTransaction();

        try {
            $exceptPlans = null;

            if ($request['exceptPlans']) {
                $exceptPlans = json_encode($request['exceptPlans']);
            }

            Coupon::create([
                'name' => $request['nameCoupon'],
                'description' => $request['description'],
                'discount' => $request['discount'],
                'create_user_id' => $request['createUserId'],
                'date_expiration' => Carbon::parse($request['dateExpiration'])->format('Y-m-d H:m:s'),
                'limit_use_per_user' => $request['limitUsePerUser'],
                'limit_use_per_coupon' => $request['limitUsePerCoupon'],
                'except_plans' => $exceptPlans,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Add Coupon',
                'response' => 'add_coupon'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD COUPON.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }
}
