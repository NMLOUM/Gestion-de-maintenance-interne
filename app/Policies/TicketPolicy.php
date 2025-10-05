<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;

class TicketPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la liste des tickets
     */
    public function viewAny(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent voir les tickets
        return true;
    }

    /**
     * Détermine si l'utilisateur peut voir un ticket spécifique
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Direction peut voir tous les tickets
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut voir tous les tickets
        if ($user->is_responsable_it) {
            return true;
        }

        // Techniciens peuvent voir tous les tickets
        if ($user->is_technician) {
            return true;
        }

        // Les employés ne peuvent voir que leurs propres tickets
        return $user->id === $ticket->requester_id;
    }

    /**
     * Détermine si l'utilisateur peut créer un ticket
     */
    public function create(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent créer des tickets
        return true;
    }

    /**
     * Détermine si l'utilisateur peut modifier un ticket
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours modifier
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours modifier
        if ($user->is_responsable_it) {
            return true;
        }

        // Techniciens NE peuvent PAS modifier les détails du ticket, seulement le statut
        // (Les détails se modifient via updateStatus ou addComment)

        // Employés peuvent modifier SEULEMENT LEURS tickets et SEULEMENT si en attente
        if ($user->is_employe && $user->id === $ticket->requester_id) {
            return $ticket->status === 'pending';
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un ticket
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Seule la direction peut supprimer des tickets
        return $user->is_direction;
    }

    /**
     * RÈGLE MÉTIER : Détermine si l'utilisateur peut assigner un ticket
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // Responsable IT et Direction peuvent assigner des tickets
        return $user->is_responsable_it || $user->is_direction;
    }

    /**
     * RÈGLE MÉTIER : Détermine si l'utilisateur peut réassigner un ticket
     */
    public function reassign(User $user, Ticket $ticket): bool
    {
        // Responsable IT et Direction peuvent réaffecter un ticket
        return $user->is_responsable_it || $user->is_direction;
    }

    /**
     * RÈGLE MÉTIER : Un ticket ne peut être "résolu" que par le technicien assigné
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours résoudre
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours résoudre
        if ($user->is_responsable_it) {
            return true;
        }

        // Seul le technicien assigné peut résoudre le ticket
        if ($ticket->assigned_to && $user->id === $ticket->assigned_to) {
            return $ticket->status === 'in_progress';
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut changer le statut d'un ticket
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours changer le statut
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours changer le statut
        if ($user->is_responsable_it) {
            return true;
        }

        // Techniciens peuvent changer le statut SEULEMENT des tickets qui LEUR sont assignés
        if ($user->is_technician && $ticket->assigned_to === $user->id) {
            // Le technicien peut seulement faire certaines transitions
            return $this->canTechnicianUpdateStatus($ticket->status);
        }

        // Employés ne peuvent PAS changer le statut
        return false;
    }

    private function canTechnicianUpdateStatus(string $currentStatus): bool
    {
        // Technicien peut : pending -> in_progress, in_progress -> resolved, resolved -> in_progress (redémarrer)
        return in_array($currentStatus, ['pending', 'in_progress', 'resolved']);
    }

    /**
     * Détermine si l'utilisateur peut démarrer un ticket
     */
    public function start(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours démarrer
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours démarrer
        if ($user->is_responsable_it) {
            return true;
        }

        // Seul le technicien assigné peut démarrer un ticket en attente
        return $user->is_technician &&
               $ticket->assigned_to === $user->id &&
               $ticket->status === 'pending';
    }

    /**
     * Détermine si l'utilisateur peut fermer un ticket
     */
    public function close(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours fermer
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours fermer
        if ($user->is_responsable_it) {
            return true;
        }

        // L'employé peut fermer son propre ticket s'il est résolu
        if ($user->id === $ticket->requester_id) {
            return $ticket->status === 'resolved';
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut annuler un ticket
     */
    public function cancel(User $user, Ticket $ticket): bool
    {
        // Direction peut annuler
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut annuler
        if ($user->is_responsable_it) {
            return true;
        }

        // Le demandeur peut annuler son ticket s'il est en attente
        return $user->id === $ticket->requester_id &&
               $ticket->status === 'pending';
    }

    /**
     * Détermine si l'utilisateur peut ajouter des commentaires
     */
    public function addComment(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours commenter
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours commenter
        if ($user->is_responsable_it) {
            return true;
        }

        // Techniciens peuvent toujours commenter
        if ($user->is_technician) {
            return true;
        }

        // Le demandeur peut commenter son ticket
        return $user->id === $ticket->requester_id;
    }

    /**
     * Détermine si l'utilisateur peut ajouter des commentaires internes
     */
    public function addInternalComment(User $user, Ticket $ticket): bool
    {
        // Direction, responsable IT et techniciens peuvent ajouter des commentaires internes
        return $user->is_direction || $user->is_responsable_it || $user->is_technician;
    }

    /**
     * Détermine si l'utilisateur peut ajouter des pièces jointes
     */
    public function addAttachment(User $user, Ticket $ticket): bool
    {
        // Direction peut toujours ajouter des pièces jointes
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut toujours ajouter des pièces jointes
        if ($user->is_responsable_it) {
            return true;
        }

        // Techniciens peuvent toujours ajouter des pièces jointes
        if ($user->is_technician) {
            return true;
        }

        // Le demandeur peut ajouter des pièces jointes à son ticket
        return $user->id === $ticket->requester_id;
    }

    /**
     * Détermine si l'utilisateur peut supprimer des pièces jointes
     */
    public function deleteAttachment(User $user, Ticket $ticket, $attachment): bool
    {
        // Direction peut supprimer toutes les pièces jointes
        if ($user->is_direction) {
            return true;
        }

        // Responsable IT peut supprimer toutes les pièces jointes
        if ($user->is_responsable_it) {
            return true;
        }

        // Techniciens peuvent supprimer les pièces jointes des tickets assignés
        if ($user->is_technician && $ticket->assigned_to === $user->id) {
            return true;
        }

        // L'utilisateur peut supprimer ses propres pièces jointes
        return isset($attachment->user_id) && $attachment->user_id === $user->id;
    }

    /**
     * Validation des transitions de statut
     */
    public function canTransitionTo(User $user, Ticket $ticket, string $newStatus): bool
    {
        $currentStatus = $ticket->status;

        // Transitions autorisées selon les règles métier
        $allowedTransitions = [
            'pending' => ['in_progress', 'cancelled'],
            'in_progress' => ['resolved', 'pending'],
            'resolved' => ['closed', 'in_progress'],
            'closed' => [], // Un ticket fermé ne peut pas changer de statut
            'cancelled' => ['pending'], // Un ticket annulé peut être réouvert
        ];

        // Vérifier si la transition est autorisée
        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return false;
        }

        // Direction et Responsable IT peuvent toujours faire des transitions valides
        if ($user->is_direction || $user->is_responsable_it) {
            return true;
        }

        // Pour les techniciens : vérifier qu'ils sont assignés au ticket
        if ($user->is_technician) {
            // Le technicien doit être assigné au ticket
            if ($ticket->assigned_to !== $user->id) {
                return false;
            }

            // Vérifications spécifiques selon le nouveau statut
            switch ($newStatus) {
                case 'in_progress':
                    // Le technicien peut démarrer un ticket en attente
                    return $currentStatus === 'pending';

                case 'resolved':
                    // Le technicien peut résoudre un ticket en cours
                    return $currentStatus === 'in_progress';

                case 'pending':
                    // Le technicien peut remettre en attente depuis "en cours"
                    return $currentStatus === 'in_progress';

                default:
                    return false;
            }
        }

        // Pour les employés
        if ($user->is_employe) {
            // L'employé peut annuler son propre ticket s'il est en attente
            if ($newStatus === 'cancelled' && $currentStatus === 'pending') {
                return $user->id === $ticket->requester_id;
            }

            // L'employé peut clôturer son propre ticket s'il est résolu
            if ($newStatus === 'closed' && $currentStatus === 'resolved') {
                return $user->id === $ticket->requester_id;
            }

            return false;
        }

        return false;
    }
}