<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Ticket;

echo "=== Test d'assignation ===" . PHP_EOL . PHP_EOL;

// Fatou Sall
$fatou = User::where('email', 'tech.batiment@maintenance.local')->first();
echo "Fatou Sall - ID: " . $fatou->id . " - Role: " . $fatou->role . PHP_EOL . PHP_EOL;

// Tickets assignés
$tickets = Ticket::where('assigned_to', $fatou->id)->get();
echo "Nombre de tickets assignés à Fatou: " . $tickets->count() . PHP_EOL . PHP_EOL;

if ($tickets->count() > 0) {
    echo "Liste des tickets:" . PHP_EOL;
    foreach ($tickets as $ticket) {
        echo "  - Ticket #{$ticket->id}: {$ticket->title} (Status: {$ticket->status})" . PHP_EOL;
    }
} else {
    echo "Aucun ticket assigné." . PHP_EOL;
}

echo PHP_EOL . "=== Dernier ticket assigné (tous techniciens) ===" . PHP_EOL;
$lastAssigned = Ticket::with('assignedUser')
    ->whereNotNull('assigned_to')
    ->latest('updated_at')
    ->first();

if ($lastAssigned) {
    echo "Ticket #{$lastAssigned->id}: {$lastAssigned->title}" . PHP_EOL;
    echo "Assigné à: " . $lastAssigned->assignedUser->name . " (ID: {$lastAssigned->assigned_to})" . PHP_EOL;
    echo "Status: {$lastAssigned->status}" . PHP_EOL;
}
