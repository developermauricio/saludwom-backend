<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValorationRequest;
use App\Models\Valuation;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ValorationController extends Controller
{
    public function createValoration(ValorationRequest $request){
        DB::beginTransaction();
        try {
            $path = Storage::disk('public')->put('/patient/signature' . auth()->user()->name.'-'.auth()->user()->last_name, file_get_contents($request->signature->data));
//            $valoration = Valuation::create([
//
//            ]);
        }catch (\Throwable $th){
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR CREATE VALORATION.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function uploadFiles(Request $request){
        $random = Str::random(10);
        $file = $request->file('file');
        $fileName = $random.'-'.$request->filename;
        $fileExtension = $file->getClientOriginalExtension();

        $nameFile = str_replace(' ', '', strtolower($fileName));
//        $path = Storage::disk('public')->put('/archives/' . $nameFile, file_get_contents($file));
        $path = Storage::disk('digitalocean')->putFileAs(env('DIGITALOCEAN_FOLDER_ARCHIVES_PATIENT'), new File($file), $nameFile, 'public');
//        $urlFinal = '/storage/archives/' . $nameFile;
        Log::info($fileExtension);


        return response()->json(['name_file' => $fileName, 'path_file' => $path]);

    }
}
