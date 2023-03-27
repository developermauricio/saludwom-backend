<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionsQuestionnaire;
use App\Models\QuestionTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class QuestionnaireController extends Controller
{
    /*=============================================
     OBTENER LOS TIPOS DE PREGUNTA (input, textarea, number, select, checkbox)
    =============================================*/
    public function getTypeQuestions(): \Illuminate\Http\JsonResponse
    {
        try {
            $typeQuestions = QuestionTypes::all();

            return response()->json([
                'success' => true,
                'message' => 'Get type questions',
                'response' => 'get_type_questions',
                'data' => $typeQuestions
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET TYPE QUESTIONS.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function getQuestionnaires()
    {
        try {
            //Obtenemos todos los cuestionarios
            $questionnaires = Questionnaire::with('treatments', 'questions.typeQuestion')->get();
            //Convertimos la fecha de registro en formato legible para el usuario
            $questionnaires->map(function ($item) {
                return $item->setAttribute('created_at_format', Date::parse($item->created_at)->locale('es')->format('l d F Y'));
            });
            //Convertimos la última fecha de actualización en formato legible para el usuario
            $questionnaires->map(function ($item) {
                return $item->setAttribute('update_at_format', Date::parse($item->update_at)->locale('es')->format('l d F Y'));
            });
            //Convertimos las preguntas en decode json para leerlas
            $questions = $questionnaires->map(function ($item) {
                return $item->questions->map(function ($ques) {
                    $ques->setAttribute('options', json_decode($ques->options));
                });
            });
            //Convertimos el tipo de pregunta en un formato legible
            $questions = $questionnaires->map(function ($item) {
                return $item->questions->map(function ($ques) {
                    $ques->setAttribute('type', $ques->typeQuestion);
                });
            });

            return response()->json([
                'success' => true,
                'message' => 'Get questionnaires',
                'response' => 'get_questionnaires',
                'data' => $questionnaires
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR GET QUESTIONNAIRES.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    /*=============================================
     AGREGAR CUESTIONARIO
    =============================================*/
    public function addQuestionnaire(Request $request)
    {
        DB::beginTransaction();
        $ilustration = null;
        $order = 1;
        try {
            // Creamos el cuestionario
            $questionnaire = Questionnaire::create([
                'name' => $request['name'],
                'description' => $request['description']
            ]);
            // Guardamos los tratamientos en la tabla relacionada con el cuestionario
            foreach ($request['treatments'] as $treatment) {
                $questionnaire->treatments()->attach($treatment['id']);
            }
            // Guardamos las preguntas
            foreach ($request['questions'] as $question) {
                /*Creamos la ilustración en formato válido y lo guardamos en el storage */
                if ($question['ilustration']) {
                    $ilustration = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadIlustrationLocal($question['ilustration']) : $this->uploadIlustrationStorage($question['ilustration']);
                }

                $questions = QuestionsQuestionnaire::create([
                    'question_type_id' => $question['type']['id'],
                    'questionnaire_id' => $questionnaire->id,
                    'question' => $question['question'],
                    'required' => $question['required'],
                    'options' => json_encode($question['optionsSelect']),
                    'order' => $order++,
                ]);
                if ($ilustration) {
                    $questions->illustration = $ilustration;
                    $questions->save();
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Add questionnaire',
                'response' => 'add_questionnaire',
                'data' => $questionnaire,

            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR ADD QUESTIONNAIRE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    /*=============================================
     ACTUALIZAR ESTADO DEL CUESTIONARIO
    =============================================*/
    public function updateStateQuestionnaire($questionnaireId, $state)
    {
        $questionnaire = Questionnaire::find($questionnaireId);

        try {
            $questionnaire->state = $state;
            $questionnaire->save();
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR UPDATE STATE QUESTIONNAIRE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    /*=============================================
     AGREGAR ILUSTRACIÓN PREGUNTA
    =============================================*/
    public function uploadIlustrationLocal($ilustration)
    {
        $randomNameSignature = 'ilustration-' . Str::random(10) . '.' . $ilustration['ext'];
        Storage::disk('public')->put('/questionnaire/' . $randomNameSignature, file_get_contents($ilustration['urlResized']));
        $urlFinal = '/storage/questionnaire/' . $randomNameSignature;
        return $urlFinal;
    }

    public function uploadIlustrationStorage($ilustration)
    {

        $randomNameSignature = 'ilustration-' . Str::random(10) . '.' . $ilustration['ext'];
        $path = Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_QUESTIONNAIRE') . '/' . $randomNameSignature, file_get_contents($ilustration['urlResized']), 'public');
        $urlFinal = env('DIGITALOCEAN_FOLDER_QUESTIONNAIRE') . '/' . $randomNameSignature;
        return $urlFinal;
    }

}
