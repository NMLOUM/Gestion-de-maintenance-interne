<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket assigné - ' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.assigned',
            with: [
                'ticket' => $this->ticket,
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