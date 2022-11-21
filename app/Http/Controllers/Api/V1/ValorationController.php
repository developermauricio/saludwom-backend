<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValorationRequest;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\SchedulesHoursMinute;
use App\Models\Valuation;
use App\Notifications\NewValuationPatientNotification;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ValorationController extends Controller
{
    public function createValoration(ValorationRequest $request)
    {
        DB::beginTransaction();
        $signature = null;
        Log::info($request);
        try {
            if ($request['signature']) {
                $signature = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadSignaturePatientLocal($request['signature']['data']) : $this->uploadSignaturePatientStorage($request['signature']['data']);
            }
            $patient = Patient::where('user_id', auth()->user()->id)->first();
            if ($signature) {
                $patient->signature = $signature;
                $patient->consent_forms = 'accept';
                $patient->save();
            }

            $valuation = Valuation::create([
                'name' => $request['name'],
                'patient_id' => $patient->id,
                'doctor_id' => $request['doctorId'],
                'type_treatment_id' => $request['selectedTreatment']['id'],
                'subscription_id' => $request['subscriptionId'],
                'objectives' => $request['objectives']
            ]);
            foreach ($request->appointments as $appointment){
                $doctorSchedule = DoctorSchedule::where('doctor_id', $appointment['doctor']['id'])->where('date',  $appointment['date'].' 00:00:00')->first();
                if ($doctorSchedule){
                    $scheduleHoursMinute = SchedulesHoursMinute::where('doctor_schedule_id',  $doctorSchedule->id)->where('hour', $appointment['onlyHour'])->where('minute', $appointment['onlyMinute'])->first();
                    $scheduleHoursMinute->state = 'SELECTED';
                    $scheduleHoursMinute->save();
                }
            }
            $patient->user()->notify(new NewValuationPatientNotification());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Create valoration',
                'response' => 'get_valoration',
                'data' => $valuation,

            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CREATE VALUATION.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function uploadSignaturePatientLocal($signature)
    {
        $randomNameSignature = 'signature-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
        Storage::disk('public')->put('/patient/signatures/' . $randomNameSignature, file_get_contents($signature));
        $urlFinal = '/storage/patient/signatures/' . $randomNameSignature;
        return $urlFinal;
    }

    public function uploadSignaturePatientStorage($signature)
    {

        $randomNameSignature = 'signature-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
        $path = Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $randomNameSignature, file_get_contents($signature), 'public');
        $urlFinal = env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $randomNameSignature;
        return $urlFinal;
    }

    public function uploadFiles(Request $request, $id)
    {
        Log::info($request);
        $random = Str::random(10);
        $file = $request->file('file');
        $fileName = $random . '-' . $request->filename;
        $urlFinal = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadFilesLocal($file, $fileName) : $this->uploadFilesStorage($file, $fileName);
        Log::info($id);
        $patient = Patient::where('user_id', $id)->first();
        $valuation = $patient->valuations()->latest()->first();

        try {
            $valuation->archives()->firstOrCreate([
                'user_id' => $id,
                'path_file' => $urlFinal,
                'name_file' =>  $request->filename
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Upload Files',
                'response' => 'upload_file',
                'path_file' => $urlFinal,
                'name_file' => $request->filename

            ], 200);
        }catch (\Throwable $th){
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPLOAD FILE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }

    }

    public function uploadFilesStorage($file, $fileName)
    {
        $path = Storage::disk('digitalocean')->putFileAs(env('DIGITALOCEAN_FOLDER_ARCHIVES_PATIENT'), new File($file), str_replace(' ', '-', $fileName), 'public');
        return $path;
    }

    public function uploadFilesLocal($file, $fileName)
    {
        $path = Storage::disk('public')->put('/patient/archives/' . str_replace(' ', '-', $fileName), file_get_contents($file));
        $urlFinal = '/storage/patient/archives/' . $fileName;
        return $urlFinal;
    }
}
