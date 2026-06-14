<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);
        return $this->success($notifications);
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();
        return $this->success(['unread_count' => $count]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return $this->success(null, 'Notificação marcada como lida.');
        }

        return $this->error('Notificação não encontrada.', 404);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return $this->success(null, 'Todas as notificações marcadas como lidas.');
    }
}
