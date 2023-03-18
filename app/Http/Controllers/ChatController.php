<?php

namespace App\Http\Controllers;

use App\Models\ChatChannel;
use App\Models\ChatMessages;
use App\Models\ChatUserValuation;
use App\Models\User;
use App\Models\Valuation;
use App\Notifications\NewMessageChatValoration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function getMessagesChatValoration($chatId)
    {
        if (!$chatId) {
            return response()->json([
                'message' => 'Not chat channel id',
                'response' => 'not_chat_channel_id',
                'success' => false,
            ], 404);
        }
        $chatUserValuation = ChatUserValuation::where('chat_channel_id', $chatId)
            ->where('user_id', auth()->user()->id)->update(['online' => true, 'receive_notification' => false]);
        $chatChannel = ChatChannel::where('id', $chatId)->first();
        $messages = ChatMessages::where('chat_channel_id', $chatId)
            ->orderBy('id', 'DESC')
            ->paginate(20);
        return response()->json([
            'message' => 'get chat messages user',
            'response' => 'get_chat_messages_user',
            'success' => true,
            'chatChannel' => $chatChannel,
            'data' => $messages,
            'not_read_at' => $messages->count(),
            'lastPage' => $messages->lastPage(),
            'total' => $messages->total()
        ], 200);
    }

    public function saveMessage(Request $request)
    {
        $chatUserValoration = ChatUserValuation::where('user_id', $request->recipient_user_id)
            ->where('chat_channel_id', $request->chat)->first();
        Log::info($chatUserValoration);

        DB::beginTransaction();
        try {
            ChatMessages::create([
                'message' => $request->message,
                'type' => $request->type,
                'chat_channel_id' => $request->chat,
                'send_user_id' => auth()->user()->id,
                'recipient_user_id' => $request->recipient_user_id
            ]);
            if ($chatUserValoration->online == 0 && $chatUserValoration->receive_notification == 1){
                Log::info('entro');
                $userNotification = User::where('id', $chatUserValoration->user_id)->first();
                $valoration = Valuation::where('id', $request->valuation_id)->first();
                $userNotification->notify(new NewMessageChatValoration($valoration, $chatUserValoration->user_id));
            }
            $chatUserValoration->update(['receive_notification' => false]);
            DB::commit();
            return response()->json([
                'message' => 'Save Message Chat',
                'response' => 'get_chat_messages_user',
                'success' => true,
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR SAVE MESSAGE CHAT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }

    public function closeOnlineChat($chatId)
    {
        DB::beginTransaction();
        try {
            $chatUserValuation = ChatUserValuation::where('chat_channel_id', $chatId)
                ->where('user_id', auth()->user()->id)->update(['online' => false, 'receive_notification' => true]);
            DB::commit();
            return response()->json([
                'message' => 'Save Message Chat',
                'response' => 'get_chat_messages_user',
                'success' => true,
                'data' => $chatUserValuation
            ], 200);

        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR SAVE MESSAGE CHAT.', $response); // Guardamos el error en el archivo de logs
            return response()->json($response, 500);
        }
    }
}
