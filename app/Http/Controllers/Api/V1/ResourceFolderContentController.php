<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ResourceFolder;
use App\Models\ResourceFolderContend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceFolderContentController extends Controller
{
    public function getResourceFiles($id)
    {
        try {
            $resourceFolder = ResourceFolder::where('id', $id)->with('archives.resourcesFolderContent.treatments')->first();
            return response()->json([
                'success' => true,
                'message' => 'Get resource folder',
                'response' => 'get_resource_folder',
                'data' => $resourceFolder->archives
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET RESOURCE FOLDER.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }


    public function addResourceFolder(Request $request)
    {
        DB::beginTransaction();
        Log::info($request);
        $file = '';
        $fileExtension = '';
        $treatments = json_decode($request->treatments);
        $storage = env('FILES_UPLOAD_PRODUCTION') === false ? 'local' : 'cloud';
        try {
            Log::info($request->pathFileIframeUrl);
            /*Creamos la firma en un formato válido y lo guardamos en el storage */
            if ($request->pathFileIframeUrl === 'true') {
                $file = $request->file('pathFile');
                $fileExtension = $file->getClientOriginalExtension();
                $file = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadResourceToFolderToLocal($file, $fileExtension) : $this->uploadSignaturePatientStorage($file);
            } else {
                $file = $request->pathFile;
            }

            $resourceFolder = ResourceFolder::find($request->folderId);

            $archiveFile = $resourceFolder->archives()->firstOrCreate([
                'user_id' => auth()->id(),
                'type_file' => $request->pathFileIframeUrl === 'true' ? strtolower($fileExtension) : 'iframe',
                'path_file' => $file,
                'name_file' => $request->name,
                'storage' => $storage
            ]);

            $resourceFolderContent = ResourceFolderContend::create([
                'name' => $request->name,
                'description' => $request->description,
                'archive_id' => $archiveFile->id
            ]);

            //Guardamos los tratamientos
            $this->addTreatments($treatments, $resourceFolderContent);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Add Resource File',
                'response' => 'add_resource_file',
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CREATE RESOURCE TO FOLDER.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function updateResourceFolder(Request $request, $id)
    {
        if (!$id) {
            return response()->json('Debe agregar un identificador');
        }

        $file = '';
        $fileExtension = '';
        $treatments = json_decode($request->treatments);
        $deleteTreatments = json_decode($request->deleteTreatments);
        $storage = env('FILES_UPLOAD_PRODUCTION') === false ? 'local' : 'cloud';
        $resourceFolderContent = ResourceFolderContend::find($id);
        try {
            if ($request->pathFileIframeUrl === 'true') {
                $file = $request->file('pathFile');
                Log::info($file);
                if ($file) {
                    $fileExtension = $file->getClientOriginalExtension();
                    $file = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadResourceToFolderToLocal($file, $fileExtension) : $this->uploadSignaturePatientStorage($file);
                } else {
                    $file = $request->pathFile;
                }

            } else {
                $file = $request->pathFile;
            }

            DB::table('archives')
                ->where('id', $request->fileId)
                ->update([
                    'user_id' => auth()->id(),
                    'type_file' => $request->typeFile === 'iframe' ? 'iframe' : ($fileExtension ? strtolower($fileExtension) : $request->typeFile),
                    'path_file' => $file,
                    'name_file' => $request->name,
                    'storage' => $storage
                ]);

            $resourceFolderContent->update([
                'name' => $request->name,
                'description' => $request->description,
                'archive_id' => $request->fileId,
                'state' => $request->state === 'true' ? 1 : 2
            ]);

            //Guardamos los tratamientos
            $this->updateTreatments($treatments, $deleteTreatments, $resourceFolderContent);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Update Resource File',
                'response' => 'update_resource_file',
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CREATE RESOURCE TO FOLDER.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    /*=============================================
     ACTUALIZAR TRATAMIENTOS
    =============================================*/
    public function updateTreatments($treatments, $deleteTreatments, $resourceFolderContent)
    {
        Log::info($deleteTreatments);
        if (count($deleteTreatments) > 0){
            foreach ($deleteTreatments as $deleteTreatment){
                $resourceFolderContent->treatments()->detach($deleteTreatment->id);
            }
        }

        foreach ($treatments as $treatment) {
             DB::table('r_folder_contents_treatmets')
                ->updateOrInsert([
                    'type_treatment_id' => $treatment->id,
                    'resource_folder_contend_id' => $resourceFolderContent->id
                ]);

        }
    }

    /*=============================================
     AGREGAR TRATAMIENTOS
    =============================================*/
    public function addTreatments($treatments, $resourceFolderContent)
    {
        foreach ($treatments as $treatment) {
            DB::table('r_folder_contents_treatmets')
                ->updateOrInsert([
                    'type_treatment_id' => $treatment->id,
                    'resource_folder_contend_id' => $resourceFolderContent->id
                ]);
        }
    }

    public function uploadResourceToFolderToLocal($file, $fileExtension): string
    {
        Log::info('ENTRO A CONVERTIR EL ARCHIVO');
        $fileNameStr = Str::random('10');
        Storage::disk('public')->put('/resources/folder/videos/' . $fileNameStr . '.' . strtolower($fileExtension), file_get_contents($file));
        return '/storage/resources/folder/videos/' . $fileNameStr . '.' . strtolower($fileExtension);

    }

//    public function uploadSignaturePatientStorage($signature): string
//    {
//
//        $randomNameSignature = 'signature-' . Str::random(10) . '-' . auth()->user()->name . '-' . auth()->user()->last_name . '.png';
//        $path = Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $randomNameSignature, file_get_contents($signature), 'public');
//        $urlFinal = env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $randomNameSignature;
//        return $urlFinal;
//    }
}
