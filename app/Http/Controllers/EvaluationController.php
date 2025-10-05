<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Evaluation;
use App\Models\User;
use App\Notifications\TicketEvaluated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Notification;

class EvaluationController extends Controller
{
    /**
     * Créer ou mettre à jour une évaluation
     */
    public function store(Request $request, Ticket $ticket)
    {
        // Vérifier que l'utilisateur est le demandeur du ticket
        if ($ticket->requester_id !== auth()->id()) {
            return back()->with('error', 'Vous ne pouvez évaluer que vos propres tickets.');
        }

        // Vérifier que le ticket est résolu ou fermé
        if (!in_array($ticket->status, ['resolved', 'closed'])) {
            return back()->with('error', 'Vous ne pouvez évaluer que les tickets résolus ou fermés.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Vérifier si une évaluation existe déjà
        $evaluation = Evaluation::where('ticket_id', $ticket->id)->first();

        if ($evaluation) {
            // Mettre à jour l'évaluation existante
            $evaluation->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $message = 'Évaluation mise à jour avec succès.';
        } else {
            // Créer une nouvelle évaluation
            $evaluation = Evaluation::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $message = 'Évaluation enregistrée avec succès. Merci pour votre retour !';
        }

        // Notifier le Responsable IT et le technicien assigné
        $usersToNotify = collect();

        // Ajouter le Responsable IT
        $responsableIt = User::where('is_responsable_it', true)->first();
        if ($responsableIt) {
            $usersToNotify->push($responsableIt);
        }

        // Ajouter le technicien assigné si différent
        if ($ticket->assignedUser && $ticket->assignedUser->id !== $responsableIt?->id) {
            $usersToNotify->push($ticket->assignedUser);
        }

        // Envoyer les notifications
        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new TicketEvaluated($ticket, $evaluation));
        }

        return back()->with('success', $message);
    }

    /**
     * Supprimer une évaluation (réservé aux admins)
     */
    public function destroy(Evaluation $evaluation)
    {
        // Seuls les admins peuvent supprimer des évaluations
        if (!auth()->user()->is_admin) {
            return back()->with('error', 'Action non autorisée.');
        }

        $evaluation->delete();

        return back()->with('success', 'Évaluation supprimée avec succès.');
    }
}
