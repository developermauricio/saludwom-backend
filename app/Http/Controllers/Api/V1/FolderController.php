<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FolderResource;
use App\Models\ResourceFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}
