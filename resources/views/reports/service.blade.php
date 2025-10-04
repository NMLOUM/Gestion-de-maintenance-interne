<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport par service - Maintenance</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .filters { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .service-section { margin-bottom: 40px; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; }
        .service-header { background-color: #28a745; color: white; padding: 10px; margin: -15px -15px 15px -15px; border-radius: 5px 5px 0 0; }
        .stats-row { display: flex; justify-content: space-around; margin-bottom: 20px; }
        .stat-item { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #28a745; }
        .stat-label { font-size: 12px; color: #6c757d; }
        .priority-breakdown { display: flex; justify-content: space-around; margin-bottom: 15px; }
        .priority-item { text-align: center; padding: 10px; border-radius: 3px; margin: 0 5px; }
        .priority-critical-bg { background-color: #f8d7da; color: #721c24; }
        .priority-high-bg { background-color: #fff3cd; color: #856404; }
        .priority-normal-bg { background-color: #d1ecf1; color: #0c5460; }
        .priority-low-bg { background-color: #d4edda; color: #155724; }
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
        <h1>Rapport par service - Maintenance</h1>
        <p>Généré le {{ date('d/m/Y à H:i') }}</p>
    </div>

    <div class="filters">
        <h3>Filtres appliqués</h3>
        <p><strong>Période :</strong> Du {{ $filters['date_from'] }} au {{ $filters['date_to'] }}</p>
        @if(isset($filters['category_id']) && $filters['category_id'])
            <p><strong>Catégorie :</strong> Filtré</p>
        @endif
        @if(isset($filters['technician_id']) && $filters['technician_id'])
            <p><strong>Technicien :</strong> Filtré</p>
        @endif
    </div>

    @php
        $serviceGroups = $tickets->groupBy('service.name');
    @endphp

    @foreach($serviceGroups as $serviceName => $serviceTickets)
    <div class="service-section">
        <div class="service-header">
            <h2>{{ $serviceName }}</h2>
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value">{{ $serviceTickets->count() }}</div>
                <div class="stat-label">Total tickets</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $serviceTickets->where('status', 'resolved')->count() }}</div>
                <div class="stat-label">Résolus</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $serviceTickets->where('status', 'in_progress')->count() }}</div>
                <div class="stat-label">En cours</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $serviceTickets->where('status', 'pending')->count() }}</div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    @php
                        $resolvedTickets = $serviceTickets->where('status', 'resolved')->whereNotNull('resolved_at');
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

        <h4>Répartition par priorité</h4>
        <div class="priority-breakdown">
            <div class="priority-item priority-critical-bg">
                <div><strong>{{ $serviceTickets->where('priority', 'critical')->count() }}</strong></div>
                <div>Critique</div>
            </div>
            <div class="priority-item priority-high-bg">
                <div><strong>{{ $serviceTickets->where('priority', 'high')->count() }}</strong></div>
                <div>Élevé</div>
            </div>
            <div class="priority-item priority-normal-bg">
                <div><strong>{{ $serviceTickets->where('priority', 'normal')->count() }}</strong></div>
                <div>Normal</div>
            </div>
            <div class="priority-item priority-low-bg">
                <div><strong>{{ $serviceTickets->where('priority', 'low')->count() }}</strong></div>
                <div>Faible</div>
            </div>
        </div>

        <h4>Tickets récents</h4>
        <table>
            <thead>
                <tr>
                    <th>N° Ticket</th>
                    <th>Titre</th>
                    <th>Demandeur</th>
                    <th>Catégorie</th>
                    <th>Priorité</th>
                    <th>Statut</th>
                    <th>Assigné à</th>
                    <th>Créé le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceTickets->sortByDesc('created_at')->take(10) as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ Str::limit($ticket->title, 40) }}</td>
                    <td>{{ $ticket->requester->name }}</td>
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
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($serviceTickets->count() > 10)
            <p style="text-align: center; color: #6c757d; font-style: italic; margin-top: 10px;">
                ... et {{ $serviceTickets->count() - 10 }} autres tickets
            </p>
        @endif
    </div>
    @endforeach

    @if($serviceGroups->isEmpty())
        <p style="text-align: center; margin: 40px 0; color: #6c757d;">
            Aucun ticket trouvé pour les critères sélectionnés.
        </p>
    @endif

    <div class="footer">
        <p>Système de maintenance - {{ config('app.name') }} - Page 1/1</p>
    </div>
</body>
</html>