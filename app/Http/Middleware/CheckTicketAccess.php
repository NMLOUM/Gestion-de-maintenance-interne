<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Ticket;

class CheckTicketAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Si on est sur une route avec un ticket spécifique
        if ($request->route('ticket')) {
            $ticket = $request->route('ticket');

            // Responsable IT et Direction : accès total à tous les tickets
            if (in_array($user->role, ['responsable_it', 'direction'])) {
                return $next($request);
            }

            // Employé : peut seulement voir SES tickets
            if ($user->role === 'employe' && $ticket->requester_id !== $user->id) {
                abort(403, 'Accès non autorisé à ce ticket.');
            }

            // Technicien : peut seulement voir les tickets qui LUI sont assignés
            if ($user->role === 'technicien' && $ticket->assigned_to !== $user->id) {
                abort(403, 'Ce ticket ne vous est pas assigné.');
            }
        }

        return $next($request);
    }
}