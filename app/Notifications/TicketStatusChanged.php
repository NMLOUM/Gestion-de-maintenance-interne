<?php
// app/Notifications/TicketStatusChanged.php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $newStatus;

    public function __construct(Ticket $ticket, string $newStatus)
    {
        $this->ticket = $ticket;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $statusLabels = [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            'cancelled' => 'Annulé',
        ];

        $message = (new MailMessage)
            ->subject('Mise à jour du ticket - ' . $this->ticket->ticket_number)
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Le statut de votre ticket a été mis à jour.')
            ->line('**Ticket:** ' . $this->ticket->title)
            ->line('**Nouveau statut:** ' . ($statusLabels[$this->newStatus] ?? $this->newStatus));

        if ($this->newStatus === 'resolved') {
            $message->line('Votre problème a été résolu. Si tout fonctionne correctement, vous pouvez fermer le ticket.');
        }

        return $message->action('Voir le ticket', route('tickets.show', $this->ticket))
                      ->line('Merci de votre patience.');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'old_status' => $this->ticket->status,
            'new_status' => $this->newStatus,
            'message' => 'Statut du ticket mis à jour: ' . $this->ticket->title,
        ];
    }
}