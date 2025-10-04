<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket assigné</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #10b981; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f8fafc; padding: 20px; }
        .ticket-info { background-color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .priority { padding: 4px 8px; border-radius: 3px; color: white; font-weight: bold; }
        .priority-low { background-color: #10b981; }
        .priority-normal { background-color: #3b82f6; }
        .priority-high { background-color: #f59e0b; }
        .priority-critical { background-color: #ef4444; }
        .footer { text-align: center; padding: 20px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ticket assigné</h1>
        </div>

        <div class="content">
            <p>Bonjour {{ $ticket->assignedUser->name }},</p>

            <p>Un ticket de maintenance vous a été assigné. Veuillez prendre en charge cette demande dans les plus brefs délais.</p>

            <div class="ticket-info">
                <h3>Détails du ticket</h3>
                <p><strong>Numéro :</strong> {{ $ticket->ticket_number }}</p>
                <p><strong>Titre :</strong> {{ $ticket->title }}</p>
                <p><strong>Description :</strong> {{ $ticket->description }}</p>
                <p><strong>Demandeur :</strong> {{ $ticket->requester->name }} ({{ $ticket->requester->email }})</p>
                <p><strong>Service :</strong> {{ $ticket->service->name }}</p>
                <p><strong>Catégorie :</strong> {{ $ticket->category->name }}</p>
                <p><strong>Priorité :</strong>
                    <span class="priority priority-{{ $ticket->priority }}">
                        @switch($ticket->priority)
                            @case('low') Faible @break
                            @case('normal') Normal @break
                            @case('high') Élevé @break
                            @case('critical') Critique @break
                        @endswitch
                    </span>
                </p>
                @if($ticket->location)
                    <p><strong>Localisation :</strong> {{ $ticket->location }}</p>
                @endif
                <p><strong>Date de création :</strong> {{ $ticket->created_at->format('d/m/Y à H:i') }}</p>
                <p><strong>Date d'assignation :</strong> {{ $ticket->assigned_at->format('d/m/Y à H:i') }}</p>
            </div>

            <p>Pour traiter ce ticket, connectez-vous au système de maintenance.</p>

            <p style="text-align: center; margin: 20px 0;">
                <a href="{{ config('app.url') }}/tickets/{{ $ticket->id }}"
                   style="background-color: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Traiter le ticket
                </a>
            </p>
        </div>

        <div class="footer">
            <p>Système de maintenance - {{ config('app.name') }}</p>
            <p>Cet email a été envoyé automatiquement, veuillez ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>