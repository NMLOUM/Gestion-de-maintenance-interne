<?php
// app/Notifications/TicketCreated.php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
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
            ->subject('Nouveau ticket de maintenance - ' . $this->ticket->ticket_number)
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Un nouveau ticket de maintenance a été créé.')
            ->line('**Titre:** ' . $this->ticket->title)
            ->line('**Demandeur:** ' . $this->ticket->requester->name)
            ->line('**Service:** ' . $this->ticket->service->name)
            ->line('**Priorité:** ' . $this->ticket->priority_display)
            ->action('Voir le ticket', route('tickets.show', $this->ticket))
            ->line('Merci de traiter cette demande rapidement.');
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'priority' => $this->ticket->priority,
            'requester_name' => $this->ticket->requester->name,
            'message' => 'Nouveau ticket créé: ' . $this->ticket->title,
        ];
    }
}