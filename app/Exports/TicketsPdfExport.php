<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class TicketsPdfExport
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function download($filename = 'tickets.pdf')
    {
        $pdf = PDF::loadView('exports.tickets-pdf', [
            'tickets' => $this->tickets,
            'date' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download($filename);
    }

    public function stream($filename = 'tickets.pdf')
    {
        $pdf = PDF::loadView('exports.tickets-pdf', [
            'tickets' => $this->tickets,
            'date' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->stream($filename);
    }
}