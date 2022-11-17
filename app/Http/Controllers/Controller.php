<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\Gender;
use App\Models\IdentificationType;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getDocumentTypes()
    {

        DB::beginTransaction();
        try {
            $documentTypes = IdentificationType::all();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Get document types',
                'response' => 'get_document_types',
                'data' => $documentTypes
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET COUNTRIES.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack(); // Hacemos un rollback para eliminar cualquier registro almacenado en la BD
            return response()->json($response, 500);
        }
    }
    public function getGenders()
    {

        DB::beginTransaction();
        try {
            $genders = Gender::all();

            return response()->json([
                'success' => true,
                'message' => 'Get genders',
                'response' => 'get_genders',
                'data' => $genders
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET GENDERS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
    public function countries()
    {

        DB::beginTransaction();
        try {
            $countries = Country::all();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Get Countries',
                'response' => 'get_countries',
                'data' => $countries
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET COUNTRIES.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack(); // Hacemos un rollback para eliminar cualquier registro almacenado en la BD
            return response()->json($response, 500);
        }
    }

    public function citiesFromCountry($country)
    {

        DB::beginTransaction();


        try {
            $countries = City::where('country_code', $country)->orderBy('name', 'asc')->get();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Get cities from country',
                'response' => 'get_cities_from_country',
                'data' => $countries
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET CITIES FROM COUNTRY.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack(); // Hacemos un rollback para eliminar cualquier registro almacenado en la BD
            return response()->json($response, 500);
        }
    }

    public function validateEmailApi($email)
    {
        $check = User::whereEmail($email)->first();
        if ($check !== null) {
            return response()->json([
                'success' => true,
                'message' => 'El correo electrónico ya ha sido registrado, por favor ingrese otro',
                'data' => 200
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'El correo electrónico no esta registrado en el sistema, puede usarlo',
                'data' => 300
            ], 200);
        }
    }

    public function checkTourPatient()
    {
        DB::beginTransaction();

        try {
            $user = User::find(auth()->id());
            $user->tour = false;
            $user->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tour check patient',
                'response' => 'tour_check_patient',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPADTE CHECK TOUR.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack(); // Hacemos un rollback para eliminar cualquier registro almacenado en la BD
            return response()->json($response, 500);
        }
    }
    public function checkDocument()
    {
        DB::beginTransaction();

        try {
            $user = User::select('document')->where('id',auth()->id())->first();

            return response()->json([
                'success' => true,
                'message' => 'Tour check document',
                'response' => 'tour_check_document',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHECK DOCUMENT.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack(); // Hacemos un rollback para eliminar cualquier registro almacenado en la BD
            return response()->json($response, 500);
        }
    }
}
