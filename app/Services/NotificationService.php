<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Ticket;
use App\Mail\TicketCreated;
use App\Mail\TicketAssigned;
use App\Mail\TicketStatusChanged;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function notifyTicketCreated(Ticket $ticket): void
    {
        // Notification interne + email pour responsable IT et direction
        $supervisors = User::whereIn('role', ['responsable_it', 'direction'])
                          ->where('is_active', true)
                          ->get();

        foreach ($supervisors as $supervisor) {
            // Créer notification interne
            Notification::createForUser(
                $supervisor->id,
                'ticket_created',
                "Nouveau ticket {$ticket->ticket_number}",
                "Créé par {$ticket->requester->name} - {$ticket->service->name} - Priorité: {$ticket->priority}",
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'priority' => $ticket->priority,
                    'service' => $ticket->service->name,
                    'category' => $ticket->category->name,
                ]
            );

            // Envoyer email (optionnel, peut être désactivé en dev)
            // Mail::to($supervisor->email)->send(new TicketCreated($ticket));
        }
    }

    public function notifyTicketAssigned(Ticket $ticket): void
    {
        $technician = $ticket->assignedUser;

        if ($technician) {
            // Notification interne pour le technicien
            Notification::createForUser(
                $technician->id,
                'ticket_assigned',
                "Ticket {$ticket->ticket_number} assigné",
                "Vous devez traiter ce ticket - Priorité: {$ticket->priority}",
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'priority' => $ticket->priority,
                    'title' => $ticket->title,
                ]
            );

            // Email au technicien
            Mail::to($technician->email)->send(new TicketAssigned($ticket));
        }

        // Notification pour le demandeur
        Notification::createForUser(
            $ticket->requester_id,
            'ticket_assigned',
            "Ticket {$ticket->ticket_number} assigné",
            "Assigné à {$technician->name}",
            $ticket->id,
            [
                'ticket_number' => $ticket->ticket_number,
                'technician_name' => $technician->name,
            ]
        );

        // Notification pour le responsable IT et la direction
        $supervisors = User::whereIn('role', ['responsable_it', 'direction'])
                          ->where('is_active', true)
                          ->where('id', '!=', auth()->id()) // Ne pas notifier si c'est lui qui a assigné
                          ->get();

        foreach ($supervisors as $supervisor) {
            Notification::createForUser(
                $supervisor->id,
                'ticket_assigned',
                "Ticket {$ticket->ticket_number} assigné",
                "Assigné à {$technician->name} par " . auth()->user()->name,
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'technician_name' => $technician->name,
                    'assigned_by' => auth()->user()->name,
                ]
            );
        }
    }

    public function notifyStatusChanged(Ticket $ticket, string $newStatus): void
    {
        $statusLabels = [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            'cancelled' => 'Annulé',
        ];

        $statusLabel = $statusLabels[$newStatus] ?? $newStatus;

        // Notification pour le demandeur
        Notification::createForUser(
            $ticket->requester_id,
            'status_changed',
            "Ticket {$ticket->ticket_number} : {$statusLabel}",
            "Statut changé par " . auth()->user()->name,
            $ticket->id,
            [
                'ticket_number' => $ticket->ticket_number,
                'old_status' => $ticket->getOriginal('status'),
                'new_status' => $newStatus,
            ]
        );

        // Email au demandeur
        Mail::to($ticket->requester->email)->send(new TicketStatusChanged($ticket, $newStatus));

        // Si assigné, notifier aussi le technicien (sauf s'il est l'auteur du changement)
        if ($ticket->assignedUser && $ticket->assignedUser->id !== auth()->id()) {
            Notification::createForUser(
                $ticket->assignedUser->id,
                'status_changed',
                "Ticket {$ticket->ticket_number} : {$statusLabel}",
                "Statut changé par " . auth()->user()->name,
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'new_status' => $newStatus,
                ]
            );
        }

        // Notification pour le responsable IT et la direction
        $supervisors = User::whereIn('role', ['responsable_it', 'direction'])
                          ->where('is_active', true)
                          ->where('id', '!=', auth()->id()) // Ne pas notifier si c'est lui qui a changé le statut
                          ->where('id', '!=', $ticket->requester_id) // Éviter doublon si le demandeur est superviseur
                          ->get();

        foreach ($supervisors as $supervisor) {
            Notification::createForUser(
                $supervisor->id,
                'status_changed',
                "Ticket {$ticket->ticket_number} : {$statusLabel}",
                "Changé par " . auth()->user()->name,
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'new_status' => $newStatus,
                    'changed_by' => auth()->user()->name,
                ]
            );
        }
    }

    public function notifyCommentAdded(Ticket $ticket, $comment): void
    {
        $usersToNotify = collect();

        // Ajouter le demandeur
        $usersToNotify->push($ticket->requester);

        // Ajouter le technicien assigné
        if ($ticket->assignedUser) {
            $usersToNotify->push($ticket->assignedUser);
        }

        // Ajouter le responsable IT et la direction (seulement pour commentaires non internes)
        if (!$comment->is_internal) {
            $supervisors = User::whereIn('role', ['responsable_it', 'direction'])
                              ->where('is_active', true)
                              ->get();

            foreach ($supervisors as $supervisor) {
                $usersToNotify->push($supervisor);
            }
        }

        // Retirer l'auteur du commentaire et dédupliquer
        $usersToNotify = $usersToNotify->filter(function ($user) use ($comment) {
            return $user->id !== $comment->user_id;
        })->unique('id');

        foreach ($usersToNotify as $user) {
            Notification::createForUser(
                $user->id,
                'comment_added',
                "Ticket {$ticket->ticket_number} : Nouveau commentaire",
                "Commentaire de {$comment->user->name}",
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'comment_author' => $comment->user->name,
                    'is_internal' => $comment->is_internal,
                ]
            );
        }
    }

    public function notifyTicketUpdated(Ticket $ticket, array $oldValues, array $newValues): void
    {
        // Notification pour le responsable IT et la direction
        $supervisors = User::whereIn('role', ['responsable_it', 'direction'])
                          ->where('is_active', true)
                          ->where('id', '!=', auth()->id()) // Ne pas notifier si c'est lui qui a modifié
                          ->get();

        // Construire description des changements
        $changes = [];
        $fieldLabels = [
            'title' => 'Titre',
            'description' => 'Description',
            'priority' => 'Priorité',
            'category_id' => 'Catégorie',
            'service_id' => 'Service',
            'location' => 'Localisation',
        ];

        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                $changes[] = $fieldLabels[$key] ?? ucfirst($key);
            }
        }

        $changesDescription = !empty($changes) ? implode(', ', $changes) : 'Informations';

        foreach ($supervisors as $supervisor) {
            Notification::createForUser(
                $supervisor->id,
                'ticket_updated',
                "Ticket {$ticket->ticket_number} modifié",
                "{$changesDescription} - Par " . auth()->user()->name,
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'updated_by' => auth()->user()->name,
                    'changes' => $changes,
                ]
            );
        }
    }

    public function notifyAttachmentAdded(Ticket $ticket, $attachment): void
    {
        // Notification pour le responsable IT et la direction
        $supervisors = User::whereIn('role', ['responsable_it', 'direction'])
                          ->where('is_active', true)
                          ->where('id', '!=', auth()->id()) // Ne pas notifier si c'est lui qui a ajouté
                          ->get();

        foreach ($supervisors as $supervisor) {
            Notification::createForUser(
                $supervisor->id,
                'attachment_added',
                "Ticket {$ticket->ticket_number} : Pièce jointe",
                "{$attachment->original_name} - Par " . auth()->user()->name,
                $ticket->id,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'file_name' => $attachment->original_name,
                    'added_by' => auth()->user()->name,
                ]
            );
        }
    }

    public function getUnreadNotificationsForUser($userId): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::forUser($userId)
                          ->unread()
                          ->with('ticket')
                          ->orderBy('created_at', 'desc')
                          ->get();
    }

    public function getNotificationsForUser($userId, $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::forUser($userId)
                          ->with('ticket')
                          ->orderBy('created_at', 'desc')
                          ->limit($limit)
                          ->get();
    }

    public function markAsRead($notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id()) {
            return $notification->markAsRead() !== null;
        }

        return false;
    }

    public function markAllAsReadForUser($userId): int
    {
        return Notification::markAllAsReadForUser($userId);
    }

    public function getUnreadCountForUser($userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    public function deleteOldNotifications($days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
                          ->delete();
    }
}