<?php
// app/Services/TicketService.php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class TicketService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function createTicket(array $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            // Créer le ticket
            $ticket = Ticket::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'requester_id' => auth()->id(),
                'service_id' => $data['service_id'],
                'category_id' => $data['category_id'],
                'priority' => $data['priority'],
                'location' => $data['location'] ?? null,
                'status' => 'pending',
            ]);

            // Ajouter à l'historique
            $this->addHistory($ticket, 'created', 'Ticket créé par ' . auth()->user()->name);

            // Gérer les pièces jointes si présentes
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->uploadAttachment($ticket, $file);
                    }
                }
            }

            // Notifier les superviseurs/admins
            $this->notificationService->notifyTicketCreated($ticket);

            return $ticket->load(['requester', 'service', 'category']);
        });
    }

    public function updateTicket(Ticket $ticket, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $data) {
            $oldValues = $ticket->only(array_keys($data));

            $ticket->update($data);

            // Enregistrer les changements dans l'historique
            $this->addHistory($ticket, 'updated', 'Ticket mis à jour par ' . auth()->user()->name, $oldValues, $data);

            // Notifier le responsable IT de la modification
            $this->notificationService->notifyTicketUpdated($ticket, $oldValues, $data);

            return $ticket->fresh();
        });
    }

    public function assignTicket(Ticket $ticket, int $technicianId): Ticket
    {
        return DB::transaction(function () use ($ticket, $technicianId) {
            $oldAssigned = $ticket->assigned_to;
            
            $ticket->update([
                'assigned_to' => $technicianId,
                'assigned_at' => now(),
            ]);

            $technician = User::find($technicianId);

            // Ajouter à l'historique
            $this->addHistory(
                $ticket,
                'assigned',
                'Ticket assigné à ' . $technician->name . ' par ' . auth()->user()->name,
                ['assigned_to' => $oldAssigned],
                ['assigned_to' => $technicianId]
            );

            // Recharger le ticket avec la relation assignedUser pour les notifications
            $ticket = $ticket->fresh(['assignedUser', 'requester']);

            // Notifier via le service de notification
            $this->notificationService->notifyTicketAssigned($ticket);

            return $ticket;
        });
    }

    public function updateStatus(Ticket $ticket, string $status): Ticket
    {
        return DB::transaction(function () use ($ticket, $status) {
            $oldStatus = $ticket->status;

            // RÈGLE MÉTIER : Vérifier les transitions de statut autorisées
            if (!$this->isValidStatusTransition($oldStatus, $status)) {
                throw new \Exception("Transition de statut invalide : {$oldStatus} -> {$status}");
            }

            // RÈGLE MÉTIER : Un ticket ne peut être "résolu" que par le technicien assigné
            if ($status === 'resolved' && !$this->canUserResolveTicket(auth()->user(), $ticket)) {
                throw new \Exception("Seul le technicien assigné peut résoudre ce ticket.");
            }

            $updateData = ['status' => $status];
            
            // Ajouter des timestamps selon le statut
            switch ($status) {
                case 'in_progress':
                    $updateData['started_at'] = now();
                    break;
                case 'resolved':
                    $updateData['resolved_at'] = now();
                    break;
                case 'closed':
                    $updateData['closed_at'] = now();
                    break;
            }

            $ticket->update($updateData);

            // Ajouter à l'historique
            $statusLabels = [
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'resolved' => 'Résolu',
                'closed' => 'Fermé',
                'cancelled' => 'Annulé',
            ];

            $this->addHistory(
                $ticket,
                'status_changed',
                'Statut changé de "' . $statusLabels[$oldStatus] . '" à "' . $statusLabels[$status] . '" par ' . auth()->user()->name,
                ['status' => $oldStatus],
                ['status' => $status]
            );

            // Notifier via le service de notification
            $this->notificationService->notifyStatusChanged($ticket, $status);

            return $ticket->fresh();
        });
    }

    public function addComment(Ticket $ticket, string $comment, bool $isInternal = false): TicketComment
    {
        $ticketComment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'comment' => $comment,
            'is_internal' => $isInternal,
        ]);

        // Ajouter à l'historique
        $this->addHistory(
            $ticket,
            'comment_added',
            ($isInternal ? 'Commentaire interne' : 'Commentaire') . ' ajouté par ' . auth()->user()->name
        );

        // Notifier les parties concernées (sauf pour les commentaires internes)
        if (!$isInternal) {
            $this->notificationService->notifyCommentAdded($ticket, $ticketComment);
        }

        return $ticketComment->load('user');
    }

    public function uploadAttachment(Ticket $ticket, UploadedFile $file): TicketAttachment
    {
        // Générer un nom unique pour le fichier
        $fileName = uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('tickets/' . $ticket->id, $fileName, 'public');

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        // Ajouter à l'historique
        $this->addHistory(
            $ticket,
            'attachment_added',
            'Fichier "' . $file->getClientOriginalName() . '" ajouté par ' . auth()->user()->name
        );

        // Notifier le responsable IT
        $this->notificationService->notifyAttachmentAdded($ticket, $attachment);

        return $attachment->load('user');
    }

    private function addHistory(Ticket $ticket, string $action, string $description, array $oldValues = null, array $newValues = null): TicketHistory
    {
        return TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }


    public function getTicketStatistics(array $filters = []): array
    {
        $query = Ticket::query();

        // Appliquer les filtres
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $tickets = $query->with(['service', 'category', 'assignedUser'])->get();

        return [
            'total' => $tickets->count(),
            'by_status' => $tickets->groupBy('status')->map->count(),
            'by_priority' => $tickets->groupBy('priority')->map->count(),
            'by_service' => $tickets->groupBy('service.name')->map->count(),
            'by_category' => $tickets->groupBy('category.name')->map->count(),
            'average_resolution_time' => $this->calculateAverageResolutionTime($tickets),
            'overdue_count' => $tickets->filter(function ($ticket) {
                return $ticket->is_overdue;
            })->count(),
        ];
    }

    private function calculateAverageResolutionTime($tickets): float
    {
        $resolvedTickets = $tickets->where('status', 'resolved')
                                  ->whereNotNull('resolved_at');

        if ($resolvedTickets->isEmpty()) {
            return 0;
        }

        $totalHours = $resolvedTickets->sum(function ($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $resolvedTickets->count(), 2);
    }

    /**
     * RÈGLE MÉTIER : Vérifier si une transition de statut est valide
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $allowedTransitions = [
            'pending' => ['in_progress', 'cancelled'],
            'in_progress' => ['resolved', 'pending'],
            'resolved' => ['validated', 'in_progress', 'closed'], // Résolu → Validation, retour en cours ou clôture
            'validated' => ['closed', 'in_progress'], // Validé → Clôture ou réouverture
            'closed' => [], // Un ticket fermé ne peut pas changer de statut
            'cancelled' => [], // Un ticket annulé ne peut pas changer de statut
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * RÈGLE MÉTIER : Vérifier si un utilisateur peut résoudre un ticket
     */
    private function canUserResolveTicket($user, Ticket $ticket): bool
    {
        // Direction et responsable IT peuvent toujours résoudre
        if ($user->is_direction || $user->is_responsable_it) {
            return true;
        }

        // Seul le technicien assigné peut résoudre
        return $ticket->assigned_to && $user->id === $ticket->assigned_to;
    }

    /**
     * RÈGLE MÉTIER : Vérifier si un ticket peut être assigné
     */
    public function canAssignTicket(Ticket $ticket): bool
    {
        // Un ticket ne peut être assigné que s'il est en attente
        return $ticket->status === 'pending';
    }

    /**
     * RÈGLE MÉTIER : Vérifier si un ticket peut être démarré
     */
    public function canStartTicket(Ticket $ticket): bool
    {
        // Un ticket ne peut être démarré que s'il est assigné et en attente
        return $ticket->status === 'pending' && !is_null($ticket->assigned_to);
    }
}