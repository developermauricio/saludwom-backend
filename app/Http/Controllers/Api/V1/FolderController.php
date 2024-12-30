<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FolderResource;
use App\Models\ResourceFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    public function getFolders(): \Illuminate\Http\JsonResponse
    {
        try {
            $folders = FolderResource::collection(
                ResourceFolder::all()
            );
            return response()->json([
                'success' => true,
                'message' => 'Get folders',
                'response' => 'get_folders',
                'data' => $folders
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET FOLDERS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function addFolder(Request $request)
    {
        DB::beginTransaction();

        try {

            $folder = ResourceFolder::create([
                'folder' => $request['folder'],
                'description' => $request['description'],
                'slug' => Str::slug(ucwords($request['folder']) . '-' . Str::random(8), '-')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Add Patient',
                'response' => 'add_patient',
                'data' => $folder,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD FOLDER.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function editFolder(Request $request, $folderId)
    {
        DB::beginTransaction();

        try {

            $resourceFolder = ResourceFolder::find($folderId);

            $nameFolder = ucwords($resourceFolder->folder) !== ucwords($request['folder']) ? ucwords($request['folder']) : ucwords($resourceFolder->folder);
            $slugFolder = ucwords($resourceFolder->folder) !== ucwords($request['folder']) ? Str::slug(ucwords($request['folder']) . '-' . Str::random(8), '-') : $resourceFolder->slug;

            $resourceFolder->update([
                'folder' => $nameFolder,
                'description' => ucwords($request['description']),
                'slug' => $slugFolder
            ]);

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
            Log::error('LOG ERROR EDIT FOLDER.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function deleteFolder($folderId)
    {
        DB::beginTransaction();
        $success = false;
        $message = 'The specialty was not removed';

        try {

            $folder = ResourceFolder::findOrFail($folderId);

            // Verificar si la carpeta tiene archivos
            if (!$folder->hasArchives()) {
                $success = true;
                $folder->delete();
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
}
