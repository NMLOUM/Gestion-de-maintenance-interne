<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .date {
            text-align: right;
            margin-bottom: 20px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .priority-high {
            color: #dc3545;
            font-weight: bold;
        }
        .priority-medium {
            color: #fd7e14;
        }
        .priority-low {
            color: #198754;
        }
        .status-open {
            color: #0d6efd;
        }
        .status-closed {
            color: #198754;
        }
        .status-in-progress {
            color: #fd7e14;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des Tickets de Maintenance</h1>
    </div>

    <div class="date">
        Généré le : {{ $date }}
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Titre</th>
                <th>Demandeur</th>
                <th>Service</th>
                <th>Catégorie</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Assigné à</th>
                <th>Date création</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ $ticket->title }}</td>
                <td>{{ $ticket->requester->name }}</td>
                <td>{{ $ticket->service->name }}</td>
                <td>{{ $ticket->category->name }}</td>
                <td class="priority-{{ strtolower($ticket->priority) }}">
                    {{ $ticket->priority_display }}
                </td>
                <td class="status-{{ str_replace(' ', '-', strtolower($ticket->status)) }}">
                    {{ $ticket->status_display }}
                </td>
                <td>{{ $ticket->assignedUser?->name ?? 'Non assigné' }}</td>
                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <h3>Résumé</h3>
        <p><strong>Nombre total de tickets :</strong> {{ $tickets->count() }}</p>
        <p><strong>Tickets ouverts :</strong> {{ $tickets->where('status', 'open')->count() }}</p>
        <p><strong>Tickets en cours :</strong> {{ $tickets->where('status', 'in_progress')->count() }}</p>
        <p><strong>Tickets fermés :</strong> {{ $tickets->where('status', 'closed')->count() }}</p>
    </div>

    <div class="footer">
        <p>Système de Maintenance - Page @pageNumber de @totalPages</p>
    </div>
</body>
</html>