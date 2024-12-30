<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Requests\RegisterSpecialty;
use App\Models\CategoryTreatment;
use App\Models\TypeTreatment;
use App\Models\Valuation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TreatmentController extends Controller
{
    public function getTreatments()
    {

        try {
            $treatments = TypeTreatment::with('categories', 'doctors.user', 'doctors.doctorSchedule')->get();

            $treatments = $treatments->each(function ($treatments, $index) {
                $treatments->sequence_number = $index + 1;
            });

            return response()->json([
                'success' => true,
                'message' => 'Get treatments',
                'response' => 'get_treatments',
                'data' => $treatments
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET TREATMENT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getTreatmentsActives()
    {

        try {
            $treatments = TypeTreatment::where('state', TypeTreatment::ACTIVE)->with('categories', 'doctors.user', 'doctors.doctorSchedule')->get();

            $treatments = $treatments->each(function ($treatments, $index) {
                $treatments->sequence_number = $index + 1;
            });

            return response()->json([
                'success' => true,
                'message' => 'Get treatments',
                'response' => 'get_treatments',
                'data' => $treatments
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET TREATMENT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function addTreatment(RegisterSpecialty $request)
    {
        DB::beginTransaction();

        try {

            $treatment = TypeTreatment::create([
                'treatment' => ucwords($request['treatment']),
                'description' => $request['description']
            ]);

            $newCategories = array_map(function ($categories) {
                return $categories['id'];
            }, $request['categories']);

            $this->addCategories($newCategories, $treatment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Add Specialty',
                'response' => 'add_specialty'
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD SPECIALTY.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function editTreatment(RegisterSpecialty $request, $treatmentId)
    {
        DB::beginTransaction();

        try {

            $treatment = TypeTreatment::find($treatmentId);

            $treatment->update([
                'treatment' => ucwords($request['treatment']),
                'description' => $request['description']
            ]);

            $currentCategories = $treatment->categories->pluck('id')->toArray();

            $newCategoriesIds = array_map(function ($categories) {
                return $categories['id'];
            }, $request['categories']);

            $categoriesToRemove = array_diff($currentCategories, $newCategoriesIds);

            $categoriesToAdd = array_diff($newCategoriesIds, $currentCategories);

            $this->addCategories($categoriesToAdd, $treatment);

            DB::table('category_type_treatment')
                ->where('type_treatment_id', $treatment->id)
                ->whereIn('category_treatment_id', $categoriesToRemove)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Edit Specialty',
                'response' => 'edit_specialty'
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

    public function addCategories($categories, $treatment)
    {
        foreach ($categories as $category) {
            DB::table('category_type_treatment')
                ->updateOrInsert([
                    'type_treatment_id' => $treatment->id,
                    'category_treatment_id' => $category
                ]);
        }
    }

    public function deleteSpecialty($specialtyId)
    {
        DB::beginTransaction();
        $success = false;
        $message = 'The specialty was not removed';

        try {

            $doctorTreatments = DB::table('doctor_type_treatment')
                ->where('type_treatment_id', $specialtyId)
                ->get();

            $valuations = Valuation::where('type_treatment_id', $specialtyId)->get();

            if (count($doctorTreatments) === 0 && count($valuations) === 0) {

                DB::table('category_type_treatment')
                    ->where('type_treatment_id', $specialtyId)
                    ->delete();

                $treatment = TypeTreatment::find($specialtyId);
                $treatment->delete();

                $success = true;
                $message = 'Delete Specialty';
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
            Log::error('LOG ERROR DELETE SPECIALTY.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function changeStatusSpecialty($specialtyId)
    {
        DB::beginTransaction();

        try {
            $treatment = TypeTreatment::findOrFail($specialtyId);
            $state = $treatment->state == TypeTreatment::ACTIVE ? TypeTreatment::INACTIVE : TypeTreatment::ACTIVE;
            $treatment->state = $state;
            $treatment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $treatment,
                'message' => 'Change Status Specialty',
                'response' => 'change_status_specialty'
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CHANGE STATUS SPECIALTY.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function getCategoriesTreatments()
    {
        try {
            $categories = CategoryTreatment::all();

            return response()->json([
                'success' => true,
                'message' => 'Get Categories Treatments',
                'response' => 'get_categories_treatments',
                'data' => $categories
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET CATEGORIES TREATMENTS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
