<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statut du ticket mis à jour</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #6366f1; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f8fafc; padding: 20px; }
        .ticket-info { background-color: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .status { padding: 4px 8px; border-radius: 3px; color: white; font-weight: bold; }
        .status-pending { background-color: #6b7280; }
        .status-in_progress { background-color: #3b82f6; }
        .status-resolved { background-color: #10b981; }
        .status-closed { background-color: #374151; }
        .status-cancelled { background-color: #ef4444; }
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
            <h1>Statut du ticket mis à jour</h1>
        </div>

        <div class="content">
            <p>Bonjour,</p>

            <p>Le statut de votre ticket de maintenance a été mis à jour.</p>

            <div class="ticket-info">
                <h3>Détails du ticket</h3>
                <p><strong>Numéro :</strong> {{ $ticket->ticket_number }}</p>
                <p><strong>Titre :</strong> {{ $ticket->title }}</p>
                <p><strong>Nouveau statut :</strong>
                    <span class="status status-{{ $newStatus }}">
                        @switch($newStatus)
                            @case('pending') En attente @break
                            @case('in_progress') En cours @break
                            @case('resolved') Résolu @break
                            @case('closed') Fermé @break
                            @case('cancelled') Annulé @break
                        @endswitch
                    </span>
                </p>
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
                @if($ticket->assignedUser)
                    <p><strong>Technicien assigné :</strong> {{ $ticket->assignedUser->name }}</p>
                @endif
                <p><strong>Date de création :</strong> {{ $ticket->created_at->format('d/m/Y à H:i') }}</p>
                <p><strong>Dernière mise à jour :</strong> {{ $ticket->updated_at->format('d/m/Y à H:i') }}</p>

                @if($newStatus === 'resolved' && $ticket->resolved_at)
                    <p><strong>Date de résolution :</strong> {{ $ticket->resolved_at->format('d/m/Y à H:i') }}</p>
                @endif

                @if($newStatus === 'closed' && $ticket->closed_at)
                    <p><strong>Date de fermeture :</strong> {{ $ticket->closed_at->format('d/m/Y à H:i') }}</p>
                @endif
            </div>

            @if($newStatus === 'resolved')
                <p><strong>Votre ticket a été résolu.</strong> Si le problème persiste ou si vous n'êtes pas satisfait de la solution, n'hésitez pas à rouvrir le ticket ou à créer un nouveau ticket.</p>
            @elseif($newStatus === 'closed')
                <p><strong>Votre ticket a été fermé.</strong> Merci d'avoir utilisé notre service de maintenance.</p>
            @elseif($newStatus === 'in_progress')
                <p><strong>Votre ticket est maintenant en cours de traitement.</strong> Notre équipe technique travaille activement sur votre demande.</p>
            @elseif($newStatus === 'cancelled')
                <p><strong>Votre ticket a été annulé.</strong> Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            @endif

            <p style="text-align: center; margin: 20px 0;">
                <a href="{{ config('app.url') }}/tickets/{{ $ticket->id }}"
                   style="background-color: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Voir le ticket
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