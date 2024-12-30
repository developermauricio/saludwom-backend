<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ResourceFolder;
use App\Models\ResourceFolderContend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceFolderContentController extends Controller
{
    public function getCategories()
    {
        try {

            $categories = Category::all();

            return response()->json([
                'success' => true,
                'message' => 'Get categories',
                'response' => 'get_categories',
                'data' => $categories
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET CATEGORIES.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }

    }

    public function getResourceFiles($id)
    {
        try {
            $resourceFolder = ResourceFolder::where('id', $id)
                ->with('archives.resourcesFolderContent.treatments', 'archives.resourcesFolderContent.categories')
                ->first();
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

        $file = '';
        $fileExtension = '';
        $treatments = json_decode($request->treatments);
        $categories = json_decode($request->categories);
        $storage = env('FILES_UPLOAD_PRODUCTION') === false ? 'local' : 'cloud';

        try {

            if ($request->typeFile !== 'iframe') {

                $file = $request->file('pathFile');
                Log::info($file->getPathname());
                $fileExtension = $file->getClientOriginalExtension();
                $file = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadResourceToFolderToLocal($file, $fileExtension) : $this->uploadResourceToFolderToStorage($file, $fileExtension);
            } else {
                $file = $request->pathFile;
            }

            $resourceFolder = ResourceFolder::find($request->folderId);

            $archiveFile = $resourceFolder->archives()->firstOrCreate([
                'user_id' => auth()->id(),
//                'type_file' => $request->pathFileIframeUrl === 'true' ? strtolower($fileExtension) : 'iframe',
                'type_file' => $request->typeFile,
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

            //Guardamos las categorías
            $this->addCategories($categories, $resourceFolderContent);

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
        $categories = json_decode($request->categories);
        $deleteTreatments = json_decode($request->deleteTreatments);
        $deleteCategories = json_decode($request->deleteCategories);
        $storage = env('FILES_UPLOAD_PRODUCTION') === false ? 'local' : 'cloud';
        $resourceFolderContent = ResourceFolderContend::find($id);
        try {
            if ($request->typeFile !== 'iframe') {
                $file = $request->file('pathFile');
                Log::info($file);
                if ($file) {
                    $fileExtension = $file->getClientOriginalExtension();
                    $file = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadResourceToFolderToLocal($file, $fileExtension) : $this->uploadResourceToFolderToStorage($file);
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
//                    'type_file' => $request->typeFile === 'iframe' ? 'iframe' : ($fileExtension ? strtolower($fileExtension) : $request->typeFile),
                    'type_file' => $request->typeFile,
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

            //Guardamos los categorias
            $this->updateCategories($categories, $deleteCategories, $resourceFolderContent);

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
        if (count($deleteTreatments) > 0) {
            foreach ($deleteTreatments as $deleteTreatment) {
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

    /*=============================================
     ACTUALIZAR CATEGORIAS
    =============================================*/
    public function updateCategories($categories, $deleteCategories, $resourceFolderContent)
    {
        Log::info($deleteCategories);
        if (count($deleteCategories) > 0) {
            foreach ($deleteCategories as $deleteCategory) {
                $resourceFolderContent->categories()->detach($deleteCategory->id);
            }
        }

        foreach ($categories as $category) {
            DB::table('category_content_resource')
                ->updateOrInsert([
                    'category_id' => $category->id,
                    'resource_folder_contend_id' => $resourceFolderContent->id
                ]);

        }
    }

    /*=============================================
     AGREGAR CATEGORIAS
    =============================================*/
    public function addCategories($categories, $resourceFolderContent)
    {
        foreach ($categories as $category) {
            DB::table('category_content_resource')
                ->updateOrInsert([
                    'category_id' => $category->id,
                    'resource_folder_contend_id' => $resourceFolderContent->id
                ]);
        }
    }

    /*=============================================
     ELIMINAR RECURSO
    =============================================*/
    public function deleteResourceFolder($resourceFolderId)
    {
        DB::beginTransaction();

        $success = false;
        $message = 'The resource was not removed';

        try {
            //resourceValuation es el recurso tanto de video, fotos, archivos etc.. que se asignan en un recurso que tiene el touch del humano en un objetivo
            $resourceValuation = DB::table('video_resource')
                ->where('resource_folder_content_id', $resourceFolderId)
                ->get();



            if (count($resourceValuation) === 0) {

                $resource = ResourceFolderContend::find($resourceFolderId);
                $resource->delete();


                $success = true;
                $message = 'Delete Plan';

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
            Log::error('LOG ERROR DELETE PLAN.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function uploadResourceToFolderToLocal($file, $fileExtension): string
    {
        ;
        $fileNameStr = Str::random('10');
        Storage::disk('public')->put('/resources/folder/videos/' . $fileNameStr . '.' . strtolower($fileExtension), file_get_contents($file));
        return '/storage/resources/folder/videos/' . $fileNameStr . '.' . strtolower($fileExtension);

    }

    public function uploadResourceToFolderToStorage($file, $fileExtension): string
    {
        $fileNameStr = Str::random('10');
        $path = Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $fileNameStr, file_get_contents($file), 'public');
        return env('DIGITALOCEAN_FOLDER_SIGNATURES_PATIENT') . '/' . $fileNameStr . '.' . strtolower($fileExtension);
    }
}
