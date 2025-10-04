<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport détaillé - Maintenance</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .filters { background-color: #f8f9fa; padding: 10px; margin-bottom: 15px; border-radius: 3px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; font-size: 10px; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .priority-critical { background-color: #f8d7da; color: #721c24; }
        .priority-high { background-color: #fff3cd; color: #856404; }
        .priority-normal { background-color: #d1ecf1; color: #0c5460; }
        .priority-low { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #e2e3e5; color: #383d41; }
        .status-in_progress { background-color: #d1ecf1; color: #0c5460; }
        .status-resolved { background-color: #d4edda; color: #155724; }
        .status-closed { background-color: #f8f9fa; color: #6c757d; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #6c757d; }
        .description { max-width: 200px; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport détaillé - Maintenance</h1>
        <p>Généré le {{ date('d/m/Y à H:i') }}</p>
        <p><strong>Total : {{ count($tickets) }} tickets</strong></p>
    </div>

    <div class="filters">
        <strong>Filtres appliqués :</strong>
        Période : {{ $filters['date_from'] }} - {{ $filters['date_to'] }}
        @if(isset($filters['service_id']) && $filters['service_id'])
            | Service filtré
        @endif
        @if(isset($filters['category_id']) && $filters['category_id'])
            | Catégorie filtrée
        @endif
        @if(isset($filters['status']) && $filters['status'])
            | Statut : {{ $filters['status'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Ticket</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Demandeur</th>
                <th>Service</th>
                <th>Catégorie</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Assigné à</th>
                <th>Créé le</th>
                <th>Résolu le</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ $ticket->title }}</td>
                <td class="description">{{ Str::limit($ticket->description, 100) }}</td>
                <td>{{ $ticket->requester->name }}</td>
                <td>{{ $ticket->service->name }}</td>
                <td>{{ $ticket->category->name }}</td>
                <td class="priority-{{ $ticket->priority }}">
                    @switch($ticket->priority)
                        @case('low') Faible @break
                        @case('normal') Normal @break
                        @case('high') Élevé @break
                        @case('critical') Critique @break
                    @endswitch
                </td>
                <td class="status-{{ $ticket->status }}">
                    @switch($ticket->status)
                        @case('pending') En attente @break
                        @case('in_progress') En cours @break
                        @case('resolved') Résolu @break
                        @case('closed') Fermé @break
                        @case('cancelled') Annulé @break
                    @endswitch
                </td>
                <td>{{ $ticket->assignedUser ? $ticket->assignedUser->name : 'Non assigné' }}</td>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if($ticket->resolved_at)
                        {{ $ticket->resolved_at->format('d/m/Y H:i') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($tickets) == 0)
        <p style="text-align: center; margin: 40px 0; color: #6c757d;">
            Aucun ticket trouvé pour les critères sélectionnés.
        </p>
    @endif

    <div class="footer">
        <p>Système de maintenance - {{ config('app.name') }} - Page 1/1</p>
    </div>
</body>
</html>