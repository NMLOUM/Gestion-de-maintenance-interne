<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $newStatus
    ) {}

    public function envelope(): Envelope
    {
        $statusLabels = [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            'cancelled' => 'Annulé',
        ];

        return new Envelope(
            subject: 'Statut du ticket mis à jour - ' . $this->ticket->ticket_number . ' (' . $statusLabels[$this->newStatus] . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.status-changed',
            with: [
                'ticket' => $this->ticket,
                'newStatus' => $this->newStatus,
                'requester' => $this->ticket->requester,
                'assignedUser' => $this->ticket->assignedUser,
                'service' => $this->ticket->service,
                'category' => $this->ticket->category,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}