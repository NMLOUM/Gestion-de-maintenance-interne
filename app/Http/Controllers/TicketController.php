<?php
// app/Http/Controllers/TicketController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\Service;
use App\Models\Category;
use App\Models\User;
use App\Models\TicketComment;
use App\Models\TicketAttachment;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $tickets = Ticket::query()
            ->with(['requester', 'assignedUser', 'service', 'category'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->priority, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->when($request->service_id, function ($query, $serviceId) {
                return $query->where('service_id', $serviceId);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->assigned_to, function ($query, $assignedTo) {
                if ($assignedTo === 'unassigned') {
                    return $query->whereNull('assigned_to');
                }
                return $query->where('assigned_to', $assignedTo);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('ticket_number', 'like', "%{$search}%");
                });
            })
            // Filtrage selon le rôle
            ->when($user->is_employe, function ($query) use ($user) {
                // Employé : seulement SES tickets
                return $query->where('requester_id', $user->id);
            })
            ->when($user->role === 'technicien', function ($query) use ($user) {
                // Technicien (pas responsable IT) : seulement les tickets qui LUI sont assignés
                return $query->where('assigned_to', $user->id);
            })
            // Responsable IT et Direction voient tous les tickets
            ->orderByRaw("
                CASE 
                    WHEN priority = 'critical' THEN 1
                    WHEN priority = 'high' THEN 2
                    WHEN priority = 'normal' THEN 3
                    WHEN priority = 'low' THEN 4
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'service_id', 'category_id', 'assigned_to', 'search']),
            'services' => Service::active()->get(),
            'categories' => Category::active()->get(),
            'technicians' => User::technicians()->active()->get(),
            'statusOptions' => $this->getStatusOptions(),
            'priorityOptions' => $this->getPriorityOptions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tickets/Create', [
            'services' => Service::active()->get(),
            'categories' => Category::active()->get(),
            'priorityOptions' => $this->getPriorityOptions(),
        ]);
    }

    public function store(StoreTicketRequest $request)
    {
        try {
            $ticket = $this->ticketService->createTicket($request->validated());
            return Redirect::route('tickets.show', $ticket)
                ->with('success', 'Ticket créé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la création du ticket: ' . $e->getMessage());
        }
    }

    public function show(Ticket $ticket)
    {
        // Vérifier les permissions avec la policy
        $this->authorize('view', $ticket);

        $ticket->load([
            'requester',
            'assignedUser',
            'service',
            'category',
            'comments' => function ($query) {
                $query->with('user')
                      ->when(!auth()->user()->is_technician, function ($q) {
                          return $q->where('is_internal', false);
                      });
            },
            'attachments.user',
            'histories.user',
            'evaluation.user'
        ]);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'technicians' => User::technicians()->active()->get(),
            'canEdit' => auth()->user()->is_admin || auth()->user()->is_technician,
            'canComment' => true,
            'canAssign' => auth()->user()->is_admin,
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        // Vérifier les permissions avec la policy
        $this->authorize('update', $ticket);

        try {
            $this->ticketService->updateTicket($ticket, $request->validated());

            return back()->with('success', 'Ticket mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function assign(Request $request, Ticket $ticket)
    {
        // Vérifier les permissions avec la policy
        $this->authorize('assign', $ticket);

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        try {
            // RÈGLE MÉTIER : Vérifier la charge de travail du technicien
            $technician = User::find($request->assigned_to);

            // Compter les tickets actifs du technicien
            $activeTicketsCount = Ticket::where('assigned_to', $request->assigned_to)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            // Bloquer si le technicien a déjà 3 tickets actifs ou plus
            if ($activeTicketsCount >= 3) {
                return back()->with('error',
                    "❌ Impossible d'assigner ce ticket à {$technician->name}. " .
                    "Ce technicien a déjà {$activeTicketsCount} tickets actifs. " .
                    "Il doit d'abord résoudre ses tickets en cours."
                );
            }

            $this->ticketService->assignTicket($ticket, $request->assigned_to);

            return back()->with('success', 'Ticket assigné avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'assignation: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed,cancelled',
        ]);

        // Vérifier les permissions avec la policy pour la transition de statut
        $this->authorize('canTransitionTo', [$ticket, $request->status]);

        try {
            $this->ticketService->updateStatus($ticket, $request->status);

            return back()->with('success', 'Statut mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la mise à jour du statut: ' . $e->getMessage());
        }
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        // Vérifier les permissions avec la policy
        if ($request->boolean('is_internal')) {
            $this->authorize('addInternalComment', $ticket);
        } else {
            $this->authorize('addComment', $ticket);
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ]);

        try {
            $this->ticketService->addComment($ticket, $request->comment, $request->boolean('is_internal'));

            return back()->with('success', 'Commentaire ajouté avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'ajout du commentaire: ' . $e->getMessage());
        }
    }

    public function uploadAttachment(Request $request, Ticket $ticket)
    {
        // Vérifier les permissions avec la policy
        $this->authorize('addAttachment', $ticket);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        try {
            $this->ticketService->uploadAttachment($ticket, $request->file('file'));

            return back()->with('success', 'Fichier ajouté avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'ajout du fichier: ' . $e->getMessage());
        }
    }

    public function deleteAttachment(TicketAttachment $attachment)
    {
        try {
            // Vérifier les permissions avec la policy
            $this->authorize('deleteAttachment', [$attachment->ticket, $attachment]);

            Storage::delete($attachment->file_path);
            $attachment->delete();

            return back()->with('success', 'Fichier supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function destroy(Ticket $ticket)
    {
        try {
            // Vérifier les permissions avec la policy
            $this->authorize('delete', $ticket);

            // Supprimer les fichiers attachés
            foreach ($ticket->attachments as $attachment) {
                Storage::delete($attachment->file_path);
            }

            $ticket->delete();

            return Redirect::route('tickets.index')
                ->with('success', 'Ticket supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    private function getStatusOptions()
    {
        return [
            ['value' => 'pending', 'label' => 'En attente'],
            ['value' => 'in_progress', 'label' => 'En cours'],
            ['value' => 'resolved', 'label' => 'Résolu'],
            ['value' => 'closed', 'label' => 'Fermé'],
            ['value' => 'cancelled', 'label' => 'Annulé'],
        ];
    }

    private function getPriorityOptions()
    {
        return [
            ['value' => 'low', 'label' => 'Faible'],
            ['value' => 'normal', 'label' => 'Normal'],
            ['value' => 'high', 'label' => 'Élevé'],
            ['value' => 'critical', 'label' => 'Critique'],
        ];
    }
}

