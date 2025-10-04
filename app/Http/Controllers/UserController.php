<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('service')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->when($request->service_id, function ($query, $serviceId) {
                return $query->where('service_id', $serviceId);
            })
            ->when($request->has('is_active'), function ($query) use ($request) {
                return $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'service_id', 'is_active']),
            'services' => Service::active()->get(),
            'roleOptions' => $this->getRoleOptions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'services' => Service::active()->get(),
            'roleOptions' => $this->getRoleOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'service_id' => 'required|exists:services,id',
            'role' => 'required|in:employee,technician,supervisor,admin',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'service_id' => $request->service_id,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $user->load(['service', 'requestedTickets', 'assignedTickets']);

        $stats = [
            'requested_tickets' => $user->requestedTickets()->count(),
            'assigned_tickets' => $user->assignedTickets()->count(),
            'resolved_tickets' => $user->assignedTickets()->where('status', 'resolved')->count(),
            'pending_tickets' => $user->assignedTickets()->where('status', 'pending')->count(),
        ];

        return Inertia::render('Users/Show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load('service'),
            'services' => Service::active()->get(),
            'roleOptions' => $this->getRoleOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'service_id' => 'required|exists:services,id',
            'role' => 'required|in:employee,technician,supervisor,admin',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'service_id' => $request->service_id,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        // Mise à jour du mot de passe si fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return back()->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        // Vérifier que l'utilisateur ne supprime pas son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Vérifier s'il y a des tickets assignés
        if ($user->assignedTickets()->whereNotIn('status', ['resolved', 'closed'])->exists()) {
            return back()->with('error', 'Cet utilisateur a des tickets en cours. Réassignez-les avant de le supprimer.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Utilisateur {$status} avec succès.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Mot de passe réinitialisé avec succès.');
    }

    private function getRoleOptions()
    {
        return [
            ['value' => 'employee', 'label' => 'Employé'],
            ['value' => 'technician', 'label' => 'Technicien'],
            ['value' => 'supervisor', 'label' => 'Superviseur'],
            ['value' => 'admin', 'label' => 'Administrateur'],
        ];
    }
}