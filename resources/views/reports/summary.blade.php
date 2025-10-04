<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de synthèse - Maintenance</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .filters { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; }
        .stat-title { font-weight: bold; color: #495057; margin-bottom: 10px; }
        .stat-value { font-size: 18px; font-weight: bold; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .priority-critical { color: #dc3545; font-weight: bold; }
        .priority-high { color: #fd7e14; font-weight: bold; }
        .priority-normal { color: #007bff; }
        .priority-low { color: #28a745; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de synthèse - Maintenance</h1>
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
        @if(isset($filters['status']) && $filters['status'])
            <p><strong>Statut :</strong> {{ $filters['status'] }}</p>
        @endif
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total des tickets</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Temps moyen de résolution</div>
            <div class="stat-value">{{ $stats['avg_resolution_time'] }} heures</div>
        </div>
    </div>

    <h3>Répartition par statut</h3>
    <table>
        <thead>
            <tr>
                <th>Statut</th>
                <th>Nombre de tickets</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['by_status'] as $status => $count)
            <tr>
                <td>
                    @switch($status)
                        @case('pending') En attente @break
                        @case('in_progress') En cours @break
                        @case('resolved') Résolu @break
                        @case('closed') Fermé @break
                        @case('cancelled') Annulé @break
                        @default {{ $status }}
                    @endswitch
                </td>
                <td>{{ $count }}</td>
                <td>{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Répartition par priorité</h3>
    <table>
        <thead>
            <tr>
                <th>Priorité</th>
                <th>Nombre de tickets</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['by_priority'] as $priority => $count)
            <tr>
                <td class="priority-{{ $priority }}">
                    @switch($priority)
                        @case('low') Faible @break
                        @case('normal') Normal @break
                        @case('high') Élevé @break
                        @case('critical') Critique @break
                        @default {{ $priority }}
                    @endswitch
                </td>
                <td>{{ $count }}</td>
                <td>{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Répartition par service</h3>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Nombre de tickets</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['by_service'] as $service => $count)
            <tr>
                <td>{{ $service ?? 'Non défini' }}</td>
                <td>{{ $count }}</td>
                <td>{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Répartition par catégorie</h3>
    <table>
        <thead>
            <tr>
                <th>Catégorie</th>
                <th>Nombre de tickets</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats['by_category'] as $category => $count)
            <tr>
                <td>{{ $category ?? 'Non défini' }}</td>
                <td>{{ $count }}</td>
                <td>{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Système de maintenance - {{ config('app.name') }} - Page 1/1</p>
    </div>
</body>
</html>