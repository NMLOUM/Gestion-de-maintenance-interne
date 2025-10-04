<?php

namespace App\Exports;

class TicketsExport
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function export()
    {
        $data = [];

        // En-têtes
        $data[] = [
            'Numéro',
            'Titre',
            'Description',
            'Demandeur',
            'Service',
            'Catégorie',
            'Priorité',
            'Statut',
            'Assigné à',
            'Localisation',
            'Date création',
            'Date résolution',
            'Heures estimées',
            'Heures réelles',
        ];

        // Données
        foreach ($this->tickets as $ticket) {
            $data[] = [
                $ticket->ticket_number,
                $ticket->title,
                $ticket->description,
                $ticket->requester->name,
                $ticket->service->name,
                $ticket->category->name,
                $ticket->priority_display,
                $ticket->status_display,
                $ticket->assignedUser?->name ?? 'Non assigné',
                $ticket->location,
                $ticket->created_at->format('d/m/Y H:i'),
                $ticket->resolved_at?->format('d/m/Y H:i') ?? '-',
                $ticket->estimated_hours ?? '-',
                $ticket->actual_hours ?? '-',
            ];
        }

        return $data;
    }

}