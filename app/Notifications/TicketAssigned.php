<?php
// app/Notifications/TicketAssigned.php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ticket assigné - ' . $this->ticket->ticket_number)
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Un ticket de maintenance vous a été assigné.')
            ->line('**Titre:** ' . $this->ticket->title)
            ->line('**Demandeur:** ' . $this->ticket->requester->name)
            ->line('**Priorité:** ' . $this->ticket->priority_display)
            ->line('**Localisation:** ' . ($this->ticket->location ?? 'Non spécifiée'))
            ->action('Voir le ticket', route('tickets.show', $this->ticket))
            ->line('Merci de traiter cette demande selon sa priorité.');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'priority' => $this->ticket->priority,
            'requester_name' => $this->ticket->requester->name,
            'message' => 'Ticket assigné: ' . $this->ticket->title,
        ];
    }
}