<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Service;
use App\Models\Category;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Redirection selon le rôle
        return match($user->role) {
            'technicien' => $this->technicienDashboard(),
            'employe' => $this->employeDashboard(),
            'responsable_it' => $this->responsableItDashboard(),
            'direction' => $this->directionDashboard(),
            default => $this->employeDashboard()
        };
    }

    private function technicienDashboard()
    {
        $userId = auth()->id();

        $stats = [
            'my_assigned_tickets' => Ticket::where('assigned_to', $userId)->count(),
            'pending_tickets' => Ticket::where('assigned_to', $userId)->where('status', 'pending')->count(),
            'in_progress_tickets' => Ticket::where('assigned_to', $userId)->where('status', 'in_progress')->count(),
            'resolved_tickets' => Ticket::where('assigned_to', $userId)->where('status', 'resolved')->count(),
            'critical_tickets' => Ticket::where('assigned_to', $userId)
                                        ->where('priority', 'critical')
                                        ->whereNotIn('status', ['resolved', 'closed'])
                                        ->count(),
        ];

        // SEULEMENT les tickets assignés à CE technicien avec SLA
        $assignedTickets = Ticket::with(['requester', 'category'])
                                ->where('assigned_to', $userId) // FILTRAGE STRICT
                                ->whereNotIn('status', ['resolved', 'closed'])
                                ->orderBy('priority', 'desc')
                                ->orderBy('created_at', 'asc')
                                ->get()
                                ->each(function ($ticket) {
                                    // Ajouter les données SLA calculées (ticket_number est déjà dans la DB)
                                    $ticket->append(['sla_status', 'sla_percentage']);
                                });

        return Inertia::render('Dashboard/Technicien', [
            'stats' => $stats,
            'assignedTickets' => $assignedTickets,
        ]);
    }

    private function employeDashboard()
    {
        $userId = auth()->id();

        $stats = [
            'my_tickets' => Ticket::where('requester_id', $userId)->count(),
            'pending' => Ticket::where('requester_id', $userId)->where('status', 'pending')->count(),
            'in_progress' => Ticket::where('requester_id', $userId)->where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('requester_id', $userId)->where('status', 'resolved')->count(),
        ];

        // SEULEMENT les tickets créés par CET employé avec dernier commentaire
        $myTickets = Ticket::with([
                                'assignedUser',
                                'category',
                                'comments' => function ($query) {
                                    $query->latest()->limit(1)->with('user');
                                }
                            ])
                          ->where('requester_id', $userId) // FILTRAGE STRICT
                          ->orderBy('created_at', 'desc')
                          ->limit(10)
                          ->get()
                          ->map(function ($ticket) {
                              // Ajouter le dernier commentaire au ticket
                              $ticket->last_comment = $ticket->comments->first();
                              unset($ticket->comments); // Nettoyer pour éviter la duplication
                              return $ticket;
                          });

        return Inertia::render('Dashboard/Employe', [
            'stats' => $stats,
            'myTickets' => $myTickets,
        ]);
    }

    private function responsableItDashboard()
    {
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // Statistiques principales avec comparaison mois précédent
        $stats = [
            'total_tickets' => Ticket::count(),
            'total_this_month' => Ticket::whereMonth('created_at', $now->month)->count(),
            'total_last_month' => Ticket::whereMonth('created_at', $lastMonth->month)->count(),
            'pending_tickets' => Ticket::where('status', 'pending')->count(),
            'in_progress_tickets' => Ticket::where('status', 'in_progress')->count(),
            'resolved_tickets' => Ticket::where('status', 'resolved')->count(),
            'validated_tickets' => Ticket::where('status', 'validated')->count(),
            'resolved_this_month' => Ticket::where('status', 'resolved')
                                           ->whereMonth('resolved_at', $now->month)
                                           ->count(),
            'critical_tickets' => Ticket::where('priority', 'critical')
                                        ->whereNotIn('status', ['resolved', 'validated', 'closed'])
                                        ->count(),
            'overdue_tickets' => $this->getOverdueTicketsCount(),
            'unassigned_tickets' => Ticket::whereNull('assigned_to')
                                          ->where('status', 'pending')
                                          ->count(),
            'avg_response_time' => $this->getAverageResponseTime(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
        ];

        // Tickets CRITIQUES non résolus (séparés des autres)
        $criticalTickets = Ticket::with(['requester', 'assignedUser', 'category'])
                                 ->where('priority', 'critical')
                                 ->whereNotIn('status', ['resolved', 'validated', 'closed'])
                                 ->orderBy('created_at', 'asc')
                                 ->limit(5)
                                 ->get();

        // Tickets NON ASSIGNÉS (en attente d'assignation)
        $unassignedTickets = Ticket::with(['requester', 'category'])
                                  ->whereNull('assigned_to')
                                  ->where('status', 'pending')
                                  ->orderBy('priority', 'desc')
                                  ->orderBy('created_at', 'asc')
                                  ->limit(10)
                                  ->get();

        // Performance des techniciens ENRICHIE
        $technicianPerformance = User::technicians()
                                    ->active()
                                    ->withCount([
                                        'assignedTickets as total_assigned',
                                        'assignedTickets as resolved_count' => function ($query) {
                                            $query->where('status', 'resolved');
                                        },
                                        'assignedTickets as in_progress_count' => function ($query) {
                                            $query->where('status', 'in_progress');
                                        },
                                        'assignedTickets as pending_count' => function ($query) {
                                            $query->where('status', 'pending');
                                        }
                                    ])
                                    ->get()
                                    ->map(function ($tech) {
                                        $tech->resolution_rate = $tech->total_assigned > 0
                                            ? round(($tech->resolved_count / $tech->total_assigned) * 100, 1)
                                            : 0;
                                        $tech->active_tickets = $tech->in_progress_count + $tech->pending_count;

                                        // Calculer le temps moyen de résolution pour ce technicien
                                        $avgTime = Ticket::where('assigned_to', $tech->id)
                                                        ->whereNotNull('resolved_at')
                                                        ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours'))
                                                        ->first();
                                        $tech->avg_resolution_hours = round($avgTime->avg_hours ?? 0, 1);

                                        return $tech;
                                    })
                                    ->sortByDesc('total_assigned');

        // Statistiques par catégorie (tickets actifs uniquement)
        $ticketsByCategory = Category::withCount(['tickets' => function ($query) {
                                        $query->whereNotIn('status', ['closed', 'resolved']);
                                    }])
                                    ->where('is_active', true)
                                    ->orderBy('tickets_count', 'desc')
                                    ->get();

        // Statistiques par priorité
        $ticketsByPriority = [
            'critical' => Ticket::where('priority', 'critical')->whereNotIn('status', ['closed'])->count(),
            'high' => Ticket::where('priority', 'high')->whereNotIn('status', ['closed'])->count(),
            'normal' => Ticket::where('priority', 'normal')->whereNotIn('status', ['closed'])->count(),
            'low' => Ticket::where('priority', 'low')->whereNotIn('status', ['closed'])->count(),
        ];

        // Tickets récents EN RETARD
        $overdueTickets = $this->getOverdueTickets();

        // Liste des techniciens disponibles pour l'assignation (triés par charge de travail)
        $technicians = User::availableTechnicians()
                          ->active()
                          ->get(['id', 'name', 'email'])
                          ->map(function ($tech) {
                              $tech->workload_display = $tech->active_tickets_count . ' ticket(s) actif(s)';
                              return $tech;
                          });

        // Activités récentes (historique partiel) - Dernières 20 actions
        $recentActivities = TicketHistory::with(['user', 'ticket'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(20)
                                        ->get()
                                        ->map(function ($history) {
                                            return [
                                                'id' => $history->id,
                                                'action' => $history->action,
                                                'description' => $history->description,
                                                'user_name' => $history->user ? $history->user->name : 'Système',
                                                'ticket_id' => $history->ticket_id,
                                                'ticket_number' => $history->ticket ? $history->ticket->ticket_number : 'N/A',
                                                'ticket_title' => $history->ticket ? $history->ticket->title : 'Ticket supprimé',
                                                'created_at' => $history->created_at,
                                            ];
                                        });

        // Performance SLA
        $slaPerformance = $this->getSLAPerformance();

        return Inertia::render('Dashboard/ResponsableIt', [
            'stats' => $stats,
            'criticalTickets' => $criticalTickets,
            'unassignedTickets' => $unassignedTickets,
            'technicianPerformance' => $technicianPerformance,
            'ticketsByCategory' => $ticketsByCategory,
            'ticketsByPriority' => $ticketsByPriority,
            'overdueTickets' => $overdueTickets,
            'technicians' => $technicians,
            'recentActivities' => $recentActivities,
            'slaPerformance' => $slaPerformance,
        ]);
    }

    private function directionDashboard()
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 'pending')->count(),
            'in_progress_tickets' => Ticket::where('status', 'in_progress')->count(),
            'resolved_tickets' => Ticket::where('status', 'resolved')->count(),
            'critical_tickets' => Ticket::where('priority', 'critical')
                                        ->whereNotIn('status', ['resolved', 'closed'])
                                        ->count(),
            'overdue_tickets' => $this->getOverdueTicketsCount(),
            'total_users' => User::active()->count(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
        ];

        // Statistiques par service
        $ticketsByService = Service::withCount(['tickets' => function ($query) {
                                    $query->whereNotIn('status', ['closed']);
                                }])
                                   ->where('is_active', true)
                                   ->get();

        // Statistiques par catégorie
        $ticketsByCategory = Category::withCount(['tickets' => function ($query) {
                                        $query->whereNotIn('status', ['closed']);
                                    }])
                                    ->where('is_active', true)
                                    ->get();

        // Performance globale
        $globalPerformance = [
            'tickets_this_month' => Ticket::whereMonth('created_at', Carbon::now()->month)->count(),
            'resolved_this_month' => Ticket::whereMonth('resolved_at', Carbon::now()->month)->count(),
            'satisfaction_rate' => 85, // À implémenter avec un système de feedback
        ];

        // Tendance des tickets (30 derniers jours)
        $ticketTrend = $this->getTicketTrend();

        // Performance SLA
        $slaPerformance = $this->getSLAPerformance();

        return Inertia::render('Dashboard/Direction', [
            'stats' => $stats,
            'ticketsByService' => $ticketsByService,
            'ticketsByCategory' => $ticketsByCategory,
            'globalPerformance' => $globalPerformance,
            'ticketTrend' => $ticketTrend,
            'slaPerformance' => $slaPerformance,
        ]);
    }

    private function getOverdueTicketsCount()
    {
        return Ticket::whereNotIn('status', ['resolved', 'closed', 'cancelled'])
                     ->where(function ($query) {
                         $query->where(function ($q) {
                             $q->where('priority', 'critical')
                               ->where('created_at', '<', Carbon::now()->subDay());
                         })
                         ->orWhere(function ($q) {
                             $q->where('priority', 'high')
                               ->where('created_at', '<', Carbon::now()->subDays(3));
                         })
                         ->orWhere(function ($q) {
                             $q->where('priority', 'normal')
                               ->where('created_at', '<', Carbon::now()->subWeek());
                         })
                         ->orWhere(function ($q) {
                             $q->where('priority', 'low')
                               ->where('created_at', '<', Carbon::now()->subDays(14));
                         });
                     })
                     ->count();
    }

    private function getTicketTrend()
    {
        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push([
                'date' => $date->format('Y-m-d'),
                'created' => Ticket::whereDate('created_at', $date)->count(),
                'resolved' => Ticket::whereDate('resolved_at', $date)->count(),
            ]);
        }
        return $days;
    }

    private function getAverageResolutionTime()
    {
        $resolvedTickets = Ticket::whereNotNull('resolved_at')
                                ->whereNotNull('created_at')
                                ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours'))
                                ->first();

        return round($resolvedTickets->avg_hours ?? 0, 1);
    }

    private function getAverageResponseTime()
    {
        // Temps moyen entre création et première assignation
        $tickets = Ticket::whereNotNull('assigned_to')
                        ->whereNotNull('created_at')
                        ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours'))
                        ->first();

        return round($tickets->avg_hours ?? 0, 1);
    }

    private function getOverdueTickets()
    {
        return Ticket::with(['requester', 'assignedUser', 'category'])
                     ->whereNotIn('status', ['resolved', 'closed', 'cancelled'])
                     ->where(function ($query) {
                         $query->where(function ($q) {
                             $q->where('priority', 'critical')
                               ->where('created_at', '<', Carbon::now()->subDay());
                         })
                         ->orWhere(function ($q) {
                             $q->where('priority', 'high')
                               ->where('created_at', '<', Carbon::now()->subDays(3));
                         })
                         ->orWhere(function ($q) {
                             $q->where('priority', 'normal')
                               ->where('created_at', '<', Carbon::now()->subWeek());
                         })
                         ->orWhere(function ($q) {
                             $q->where('priority', 'low')
                               ->where('created_at', '<', Carbon::now()->subDays(14));
                         });
                     })
                     ->orderBy('created_at', 'asc')
                     ->limit(10)
                     ->get();
    }

    private function getSLAPerformance()
    {
        $now = Carbon::now();

        // Tickets actifs (non résolus/fermés)
        $activeTickets = Ticket::whereNotIn('status', ['resolved', 'closed', 'cancelled'])->get();

        $slaData = [
            'critical' => ['total' => 0, 'on_time' => 0, 'overdue' => 0, 'sla_hours' => 24],
            'high' => ['total' => 0, 'on_time' => 0, 'overdue' => 0, 'sla_hours' => 72],
            'normal' => ['total' => 0, 'on_time' => 0, 'overdue' => 0, 'sla_hours' => 168],
            'low' => ['total' => 0, 'on_time' => 0, 'overdue' => 0, 'sla_hours' => 336],
        ];

        foreach ($activeTickets as $ticket) {
            $priority = $ticket->priority;
            if (!isset($slaData[$priority])) continue;

            $slaData[$priority]['total']++;

            $hoursElapsed = $ticket->created_at->diffInHours($now);
            $slaHours = $slaData[$priority]['sla_hours'];

            if ($hoursElapsed > $slaHours) {
                $slaData[$priority]['overdue']++;
            } else {
                $slaData[$priority]['on_time']++;
            }
        }

        // Calculer les pourcentages
        foreach ($slaData as $priority => &$data) {
            if ($data['total'] > 0) {
                $data['on_time_percent'] = round(($data['on_time'] / $data['total']) * 100, 1);
                $data['overdue_percent'] = round(($data['overdue'] / $data['total']) * 100, 1);
            } else {
                $data['on_time_percent'] = 0;
                $data['overdue_percent'] = 0;
            }
        }

        // Performance SLA globale
        $totalActive = array_sum(array_column($slaData, 'total'));
        $totalOnTime = array_sum(array_column($slaData, 'on_time'));
        $totalOverdue = array_sum(array_column($slaData, 'overdue'));

        return [
            'by_priority' => $slaData,
            'global' => [
                'total' => $totalActive,
                'on_time' => $totalOnTime,
                'overdue' => $totalOverdue,
                'on_time_percent' => $totalActive > 0 ? round(($totalOnTime / $totalActive) * 100, 1) : 0,
            ]
        ];
    }

    /**
     * Afficher l'historique complet pour le Responsable IT
     */
    public function history(Request $request)
    {
        // Vérifier que l'utilisateur est responsable IT ou direction
        if (!auth()->user()->is_responsable_it && !auth()->user()->is_direction) {
            abort(403, 'Accès non autorisé');
        }

        $query = TicketHistory::with(['user', 'ticket.requester', 'ticket.assignedUser'])
                              ->latest();

        // Filtres
        if ($request->filled('ticket_id')) {
            $query->where('ticket_id', $request->ticket_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination
        $histories = $query->paginate(50)->withQueryString();

        // Liste des utilisateurs pour le filtre
        $users = User::active()->orderBy('name')->get(['id', 'name', 'role']);

        // Types d'actions disponibles
        $actions = [
            'created' => 'Créé',
            'assigned' => 'Assigné',
            'status_changed' => 'Statut modifié',
            'comment_added' => 'Commentaire ajouté',
            'attachment_added' => 'Pièce jointe ajoutée',
            'updated' => 'Mis à jour',
        ];

        return Inertia::render('Dashboard/History', [
            'histories' => $histories,
            'users' => $users,
            'actions' => $actions,
            'filters' => $request->only(['ticket_id', 'user_id', 'action', 'date_from', 'date_to']),
        ]);
    }
}