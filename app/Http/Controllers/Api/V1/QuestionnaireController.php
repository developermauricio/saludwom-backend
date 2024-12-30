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

    public function getQuestionnaires(Request $request)
    {
        try {

            $query = Questionnaire::with('treatments', 'questions.typeQuestion', 'categories')->latest('created_at');

            if ($request->treatmentId) {
                $query->whereHas('treatments', function ($q) use ($request) {
                    $q->where('type_treatment_id', $request->treatmentId);
                });
            }

            if ($request->categoryId) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('category_id', $request->categoryId);
                });
            }

            $questionnaires = $query->get();

//            // Transformación de fechas
            $questionnaires->each(function ($item) {
                $item->setAttribute('created_at_format', Date::parse($item->created_at)->locale('es')->format('l d F Y H:i:s'));
                $item->setAttribute('update_at_format', Date::parse($item->updated_at)->locale('es')->format('l d F Y H:i:s'));
            });

            // Manipulación de preguntas y opciones
            $questionnaires->each(function ($item) {
                $item->questions->each(function ($ques) {
                    $ques->setAttribute('options', json_decode($ques->options));
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

        try {
            // Creamos el cuestionario
            $questionnaire = Questionnaire::create([
                'name' => $request['name'],
                'description' => $request['description']
            ]);
            // Guardamos los tratamientos en la tabla relacionada con el cuestionario
            $this->addTreatments($request['treatments'], $questionnaire);
            // Guardamos las preguntas
            $this->addQuestions($request['questions'], $questionnaire);

            $this->addCategories($request['categories'], $questionnaire);

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
     AGREGAR TRATAMIENTOS
    =============================================*/
    public function addTreatments($treatments, $questionnaire)
    {
        foreach ($treatments as $treatment) {
            $treatment = DB::table('questionnaire_treatment')
                ->updateOrInsert([
                    'type_treatment_id' => $treatment['id'],
                    'questionnaire_id' => $questionnaire->id
                ]);
        }
    }

    /*=============================================
     AGREGAR CATEGORIAS
    =============================================*/
    public function addCategories($categories, $questionnaire)
    {
        foreach ($categories as $category) {
            $treatment = DB::table('category_questionnaire')
                ->updateOrInsert([
                    'category_id' => $category['id'],
                    'questionnaire_id' => $questionnaire->id
                ]);
        }
    }

    /*=============================================
     ACTUALIZAR TRATAMIENTOS
    =============================================*/
    public function updateTreatments($treatments, $deleteTreatments, $questionnaire)
    {
        if ($deleteTreatments && count($deleteTreatments) > 0) {
            foreach ($deleteTreatments as $deleteTreatment) {
                $questionnaire->treatments()->detach($deleteTreatment['id']);
            }
        }

        foreach ($treatments as $treatment) {
            $treatment = DB::table('questionnaire_treatment')
                ->updateOrInsert([
                    'type_treatment_id' => $treatment['id'],
                    'questionnaire_id' => $questionnaire->id
                ]);

        }
    }

    /*=============================================
     ACTUALIZAR CATEGORIAS
    =============================================*/
    public function updateCategories($categories, $deleteCategories, $questionnaire)
    {
        if ($deleteCategories && count($deleteCategories) > 0) {
            foreach ($deleteCategories as $deleteCategory) {
                $questionnaire->categories()->detach($deleteCategory['id']);
            }
        }

        foreach ($categories as $category) {
            $treatment = DB::table('category_questionnaire')
                ->updateOrInsert([
                    'category_id' => $category['id'],
                    'questionnaire_id' => $questionnaire->id
                ]);

        }
    }

    /*=============================================
         AGREGAR PREGUNTAS
        =============================================*/
    public function addQuestions($questions, $questionnaire)
    {
        $illustration = null;
        $order = 1;
        foreach ($questions as $question) {
            /*Creamos la ilustración en formato válido y lo guardamos en el storage */
            if ($question['illustration']) {
                $illustration = env('FILES_UPLOAD_PRODUCTION') === false ? $this->uploadIllustrationLocal($question['illustration']) : $this->uploadIllustrationStorage($question['illustration']);
            }

            $questionIn = QuestionsQuestionnaire::firstOrCreate([
                'question_type_id' => $question['type_question']['id'],
                'questionnaire_id' => $questionnaire->id,
                'question' => $question['question'],
                'required' => $question['required'],
                'options' => json_encode($question['options']),
            ]);


            if ($illustration && $question['illustration']) {
                Log::info($illustration);
                $questionIn->illustration = $illustration;
                $questionIn->save();
            }
            $q = QuestionsQuestionnaire::where('id', $questionIn->id)
                ->update(['order' => $order]);
            $order++;
        }
    }

    public function removeQuestions($questions)
    {
        if (count($questions) > 0) {

            foreach ($questions as $question) {
                QuestionsQuestionnaire::where('id', $question['id'])
                    ->delete();
            }
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

    public function updateQuestionnaire(Request $request, $id)
    {
        if (!$id) {
            return response()->json('Debe agregar un identificador');
        }
        //Actualizar datos del cuestionario
        $questionnaire = Questionnaire::find($id);
        $questionnaire->update([
            'name' => $request['name'],
            'description' => $request['description']
        ]);
        //Guardamos los tratamientos
        $this->updateTreatments($request['treatments'], $request['deleteTreatments'], $questionnaire);

        $this->updateCategories($request['categories'], $request['deleteCategories'], $questionnaire);

        //Guardamos las preguntas
        $this->addQuestions($request['questions'], $questionnaire);

        //Eliminar preguntas
        if (count($request['removeQuestions']) > 0) {
            $this->removeQuestions($request['removeQuestions']);
        }
    }

    public function deleteQuestionnaire($id)
    {
        DB::beginTransaction();
        try {
            $questionnaire = Questionnaire::find($id);
            //Eliminamos las preguntas relacionadas
            QuestionsQuestionnaire::where('questionnaire_id', $id)
                ->delete();
            //Eliminamos los tratamientos relacionados
            DB::table('questionnaire_treatment')
                ->where('questionnaire_id', $id)
                ->delete();
            //Eliminamos las categorias
            DB::table('category_questionnaire')
                ->where('questionnaire_id', $id)
                ->delete();
            //Eliminamos el cuestionario
            $questionnaire = Questionnaire::find($id);
            $resourceQuestionnaire = DB::table('questionnaire_resource')
                ->where('questionnaire_id', $id)
                ->get();
            Log::info($resourceQuestionnaire);
            if (count($resourceQuestionnaire) > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Questionnaire cannot be deleted',
                    'response' => 'questionnaire_cannot_be_delete',
                    'data' => $questionnaire,
                ], 201);
            } else {
                $questionnaire->delete();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Questionnaire Delete',
                    'response' => 'questionnaire_delete',
                    'data' => $questionnaire,
                ], 200);

            }


        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR DELETE QUESTIONNAIRE.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    /*=============================================
     AGREGAR ILUSTRACIÓN PREGUNTA
    =============================================*/
    public function uploadIllustrationLocal($illustration)
    {
        if (is_string($illustration) < 1) {
            $randomNameSignature = 'illustration-' . Str::random(10) . '.' . $illustration['ext'];
            Storage::disk('public')->put('/questionnaire/' . $randomNameSignature, file_get_contents($illustration['urlResized']));
            return '/storage/questionnaire/' . $randomNameSignature;
        }
    }

    public function uploadIllustrationStorage($illustration)
    {
        if (is_string($illustration) < 1) {
            $randomNameSignature = 'ilustration-' . Str::random(10) . '.' . $illustration['ext'];
            Storage::disk('digitalocean')->put(env('DIGITALOCEAN_FOLDER_QUESTIONNAIRE') . '/' . $randomNameSignature, file_get_contents($illustration['urlResized']), 'public');
            return env('DIGITALOCEAN_FOLDER_QUESTIONNAIRE') . '/' . $randomNameSignature;
        }
    }

}
