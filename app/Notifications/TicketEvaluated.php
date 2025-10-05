<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\Evaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketEvaluated extends Notification
{
    use Queueable;

    public $ticket;
    public $evaluation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, Evaluation $evaluation)
    {
        $this->ticket = $ticket;
        $this->evaluation = $evaluation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $stars = str_repeat('⭐', $this->evaluation->rating);
        $satisfactionLevel = $this->evaluation->rating >= 4 ? '😊 Satisfait' :
                             ($this->evaluation->rating >= 3 ? '😐 Neutre' : '😞 Insatisfait');

        return [
            'title' => '⭐ Nouvelle évaluation',
            'message' => "Le ticket #{$this->ticket->ticket_number} a été évalué : {$stars} ({$satisfactionLevel})",
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'rating' => $this->evaluation->rating,
            'comment' => $this->evaluation->comment,
            'evaluator' => $this->ticket->requester->name,
            'url' => route('tickets.show', $this->ticket->id),
            'type' => 'evaluation',
            'icon' => '⭐',
            'color' => $this->evaluation->rating >= 4 ? 'green' :
                      ($this->evaluation->rating >= 3 ? 'yellow' : 'red')
        ];
    }
}
