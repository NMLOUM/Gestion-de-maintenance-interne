<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport par technicien - Maintenance</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .filters { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .technician-section { margin-bottom: 40px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; }
        .technician-header { background-color: #007bff; color: white; padding: 10px; margin: -15px -15px 15px -15px; border-radius: 5px 5px 0 0; }
        .stats-row { display: flex; justify-content: space-around; margin-bottom: 20px; }
        .stat-item { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 12px; color: #6c757d; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .priority-critical { color: #dc3545; font-weight: bold; }
        .priority-high { color: #fd7e14; font-weight: bold; }
        .priority-normal { color: #007bff; }
        .priority-low { color: #28a745; }
        .status-resolved { color: #28a745; font-weight: bold; }
        .status-in_progress { color: #007bff; }
        .status-pending { color: #6c757d; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport par technicien - Maintenance</h1>
        <p>Généré le {{ date('d/m/Y à H:i') }}</p>
    </div>

    <div class="filters">
        <h3>Filtres appliqués</h3>
        <p><strong>Période :</strong> Du {{ $filters['date_from'] }} au {{ $filters['date_to'] }}</p>
        @if(isset($filters['service_id']) && $filters['service_id'])
            <p><strong>Service :</strong> Filtré</p>
        @endif
        @if(isset($filters['category_id']) && $filters['category_id'])
            <p><strong>Catégorie :</strong> Filtré</p>
        @endif
    </div>

    @php
        $technicianGroups = $tickets->groupBy('assignedUser.name');
        $unassignedTickets = $tickets->whereNull('assigned_to');
    @endphp

    @foreach($technicianGroups as $technicianName => $technicianTickets)
        @if($technicianName)
        <div class="technician-section">
            <div class="technician-header">
                <h2>{{ $technicianName }}</h2>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value">{{ $technicianTickets->count() }}</div>
                    <div class="stat-label">Total tickets</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $technicianTickets->where('status', 'resolved')->count() }}</div>
                    <div class="stat-label">Résolus</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $technicianTickets->where('status', 'in_progress')->count() }}</div>
                    <div class="stat-label">En cours</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">
                        @php
                            $resolvedTickets = $technicianTickets->where('status', 'resolved')->whereNotNull('resolved_at');
                            if($resolvedTickets->count() > 0) {
                                $totalHours = $resolvedTickets->sum(function($ticket) {
                                    return $ticket->created_at->diffInHours($ticket->resolved_at);
                                });
                                echo round($totalHours / $resolvedTickets->count(), 1);
                            } else {
                                echo '-';
                            }
                        @endphp
                    </div>
                    <div class="stat-label">Temps moyen (h)</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>N° Ticket</th>
                        <th>Titre</th>
                        <th>Service</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Résolu le</th>
                        <th>Durée (h)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($technicianTickets as $ticket)
                    <tr>
                        <td>{{ $ticket->ticket_number }}</td>
                        <td>{{ Str::limit($ticket->title, 40) }}</td>
                        <td>{{ $ticket->service->name }}</td>
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
                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($ticket->resolved_at)
                                {{ $ticket->resolved_at->format('d/m/Y H:i') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($ticket->resolved_at)
                                {{ $ticket->created_at->diffInHours($ticket->resolved_at) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endforeach

    @if($unassignedTickets->count() > 0)
    <div class="technician-section">
        <div class="technician-header">
            <h2>Tickets non assignés</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>N° Ticket</th>
                    <th>Titre</th>
                    <th>Service</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unassignedTickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ Str::limit($ticket->title, 50) }}</td>
                    <td>{{ $ticket->service->name }}</td>
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
                    <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Système de maintenance - {{ config('app.name') }} - Page 1/1</p>
    </div>
</body>
</html>