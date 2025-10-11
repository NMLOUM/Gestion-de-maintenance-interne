<?php
// Script de débogage temporaire
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Récupérer l'utilisateur connecté (remplacez l'ID par celui du technicien)
echo "Entrez l'ID du technicien à vérifier: ";
$userId = trim(fgets(STDIN));

$user = \App\Models\User::find($userId);

if (!$user) {
    echo "❌ Utilisateur non trouvé avec l'ID: $userId\n";
    exit;
}

echo "\n=== INFORMATIONS UTILISATEUR ===\n";
echo "ID: {$user->id}\n";
echo "Nom: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Rôle (colonne): {$user->role}\n";
echo "\n=== ATTRIBUTS CALCULÉS ===\n";
echo "is_admin: " . ($user->is_admin ? 'OUI' : 'NON') . "\n";
echo "is_technician: " . ($user->is_technician ? 'OUI' : 'NON') . "\n";
echo "is_responsable_it: " . ($user->is_responsable_it ? 'OUI' : 'NON') . "\n";
echo "is_direction: " . ($user->is_direction ? 'OUI' : 'NON') . "\n";
echo "is_employe: " . ($user->is_employe ? 'OUI' : 'NON') . "\n";

echo "\n=== TICKETS ASSIGNÉS ===\n";
$assignedTickets = $user->assignedTickets()->count();
echo "Nombre de tickets assignés: $assignedTickets\n";

if ($assignedTickets > 0) {
    echo "\nListe des tickets:\n";
    foreach ($user->assignedTickets as $ticket) {
        echo "  - Ticket #{$ticket->ticket_number} (Statut: {$ticket->status})\n";
    }
}

echo "\n=== TEST DE PERMISSIONS ===\n";
$testTicket = \App\Models\Ticket::where('assigned_to', $user->id)->first();

if ($testTicket) {
    echo "Test avec le ticket #{$testTicket->ticket_number}:\n";
    echo "  - Statut actuel: {$testTicket->status}\n";
    echo "  - Assigné à (ID): {$testTicket->assigned_to}\n";
    echo "  - ID de l'utilisateur: {$user->id}\n";
    echo "  - Correspondent: " . ($testTicket->assigned_to === $user->id ? 'OUI ✅' : 'NON ❌') . "\n";

    // Test des transitions
    $policy = new \App\Policies\TicketPolicy();

    if ($testTicket->status === 'pending') {
        $canStart = $policy->canTransitionTo($user, $testTicket, 'in_progress');
        echo "  - Peut démarrer (pending → in_progress): " . ($canStart ? 'OUI ✅' : 'NON ❌') . "\n";
    }

    if ($testTicket->status === 'in_progress') {
        $canResolve = $policy->canTransitionTo($user, $testTicket, 'resolved');
        echo "  - Peut résoudre (in_progress → resolved): " . ($canResolve ? 'OUI ✅' : 'NON ❌') . "\n";
    }
} else {
    echo "Aucun ticket assigné à cet utilisateur pour tester.\n";
}

echo "\n";
