<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Récupérer toutes les notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $notifications = $this->notificationService->getNotificationsForUser(
            auth()->id(),
            $limit
        );

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCountForUser(auth()->id())
        ]);
    }

    /**
     * Récupérer les notifications non lues
     */
    public function unread()
    {
        $notifications = $this->notificationService->getUnreadNotificationsForUser(auth()->id());

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $success = $this->notificationService->markAsRead($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marquée comme lue' : 'Notification non trouvée'
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsReadForUser(auth()->id());

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} notification(s) marquée(s) comme lue(s)"
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function count()
    {
        $count = $this->notificationService->getUnreadCountForUser(auth()->id());

        return response()->json([
            'count' => $count
        ]);
    }
}
