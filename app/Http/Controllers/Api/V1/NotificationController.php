<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function getNotifications($idUser)
    {
        if (!$idUser){
            return response()->json([
                'message' => 'Not user notification',
                'response' => 'not_user_notification',
                'success' => false,
            ], 404);
        }
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $idUser)
            ->orderByDesc('created_at')
            ->paginate(10);

        $notificationsNotRead_at = DB::table('notifications')
            ->where('notifiable_id', $idUser)
            ->where('read_at', null)
            ->get();


        return response()->json([
            'message' => 'get notifications user',
            'response' => 'get_notifications_user',
            'success' => true,
            'data' => $notifications,
            'not_read_at' => $notificationsNotRead_at->count(),
            'lastPage' => $notifications->lastPage(),
            'total' => $notifications->total()
        ], 200);
    }

    public function readAtNotifications($notification)
    {
        DB::beginTransaction();
        try {
            DB::table('notifications')
                ->where('id', $notification)
                ->update(['read_at' => now()]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Notification Read',
                'response' => 'notification_read',
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR READ AT NOTIFICATION.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }
    public function markNotificationAsRead(){
        DB::beginTransaction();
        try {
            DB::table('notifications')
                ->where('notifiable_id', auth()->user()->id)
                ->update(['read_at' => now()]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Notification Read',
                'response' => 'notification_read',
            ], 200);
        } catch (\Throwable $th) {
            $response = [
                'success' => false,
                'message' => 'Transaction Error',
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ];
            Log::error('LOG ERROR READ AT NOTIFICATION.', $response); // Guardamos el error en el archivo de logs
            DB::rollBack();
            return response()->json($response, 500);
        }
    }
}
