<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12px;
            opacity: 0.9;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 10px;
            margin-bottom: 15px;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 3px 0;
        }

        .info-box strong {
            color: #667eea;
        }

        h2 {
            color: #667eea;
            font-size: 16px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
            margin: 20px 0 10px 0;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
            background: #f8f9fa;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }

        .stat-card .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }

        .stat-card.green { border-left: 4px solid #10b981; }
        .stat-card.green .number { color: #10b981; }

        .stat-card.orange { border-left: 4px solid #f59e0b; }
        .stat-card.orange .number { color: #f59e0b; }

        .stat-card.red { border-left: 4px solid #ef4444; }
        .stat-card.red .number { color: #ef4444; }

        .stat-card.gray { border-left: 4px solid #6b7280; }
        .stat-card.gray .number { color: #6b7280; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #e0e0e0;
            padding: 8px;
            text-align: left;
        }

        table.data-table th {
            background: #667eea;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        table.data-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge.green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge.orange {
            background: #fed7aa;
            color: #92400e;
        }

        .badge.red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge.gray {
            background: #e5e7eb;
            color: #374151;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            text-align: center;
            color: white;
            font-size: 10px;
            line-height: 20px;
            font-weight: bold;
        }

        .progress-fill.green { background: #10b981; }
        .progress-fill.orange { background: #f59e0b; }
        .progress-fill.red { background: #ef4444; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Période: {{ $period }}</div>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td width="50%"><strong>Généré le:</strong> {{ $generated_at }}</td>
                <td><strong>Généré par:</strong> {{ $generated_by }}</td>
            </tr>
        </table>
    </div>

    <h2>📊 Résumé de Performance SLA</h2>

    <div class="stats-grid">
        <div class="stat-card gray">
            <div class="label">Total Tickets</div>
            <div class="number">{{ $slaStats['total'] }}</div>
        </div>
        <div class="stat-card green">
            <div class="label">Dans les temps</div>
            <div class="number">{{ $slaStats['on_time'] }}</div>
            <div class="label">{{ $slaStats['total'] > 0 ? round(($slaStats['on_time'] / $slaStats['total']) * 100, 1) : 0 }}%</div>
        </div>
        <div class="stat-card orange">
            <div class="label">En alerte</div>
            <div class="number">{{ $slaStats['warning'] }}</div>
            <div class="label">{{ $slaStats['total'] > 0 ? round(($slaStats['warning'] / $slaStats['total']) * 100, 1) : 0 }}%</div>
        </div>
        <div class="stat-card red">
            <div class="label">Dépassés</div>
            <div class="number">{{ $slaStats['overdue'] + $slaStats['critical'] }}</div>
            <div class="label">{{ $slaStats['total'] > 0 ? round((($slaStats['overdue'] + $slaStats['critical']) / $slaStats['total']) * 100, 1) : 0 }}%</div>
        </div>
    </div>

    <h2>🎯 Performance SLA par Priorité</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Priorité</th>
                <th>Total Tickets</th>
                <th>Dans les temps</th>
                <th>Taux de respect SLA</th>
                <th>Progression</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['critical' => 'Critique', 'high' => 'Élevé', 'normal' => 'Normal', 'low' => 'Faible'] as $key => $label)
                @if(isset($slaByPriority[$key]))
                <tr>
                    <td>
                        <span class="badge {{ $key === 'critical' ? 'red' : ($key === 'high' ? 'orange' : 'gray') }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td>{{ $slaByPriority[$key]['total'] }}</td>
                    <td>{{ $slaByPriority[$key]['on_time'] }}</td>
                    <td><strong>{{ $slaByPriority[$key]['percentage'] }}%</strong></td>
                    <td>
                        @php
                            $percentage = $slaByPriority[$key]['percentage'];
                            $colorClass = $percentage >= 80 ? 'green' : ($percentage >= 60 ? 'orange' : 'red');
                        @endphp
                        <div class="progress-bar">
                            <div class="progress-fill <?php echo $colorClass; ?>"
                                 style="width: <?php echo $percentage; ?>%">
                                <?php echo $percentage; ?>%
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <h2>📋 Détail des Tickets</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>N° Ticket</th>
                <th>Titre</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Créé le</th>
                <th>Statut SLA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ Str::limit($ticket->title, 40) }}</td>
                <td>
                    <span class="badge {{ $ticket->priority === 'critical' ? 'red' : ($ticket->priority === 'high' ? 'orange' : 'gray') }}">
                        @php
                            $priorities = ['critical' => 'Critique', 'high' => 'Élevé', 'normal' => 'Normal', 'low' => 'Faible'];
                        @endphp
                        {{ $priorities[$ticket->priority] ?? ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td>
                    <span class="badge gray">
                        @php
                            $statuses = ['open' => 'Ouvert', 'pending' => 'En attente', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Fermé'];
                        @endphp
                        {{ $statuses[$ticket->status] ?? ucfirst($ticket->status) }}
                    </span>
                </td>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @php
                        $slaStatus = $ticket->sla_status['status'] ?? 'ok';
                        $slaLabel = $ticket->sla_status['label'] ?? 'OK';
                        $badgeClass = $slaStatus === 'ok' || $slaStatus === 'completed' ? 'green' : ($slaStatus === 'warning' ? 'orange' : 'red');
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ $slaLabel }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Rapport généré automatiquement par le système de gestion de maintenance</p>
        <p>{{ now()->format('d/m/Y à H:i:s') }}</p>
    </div>
</body>
</html>
