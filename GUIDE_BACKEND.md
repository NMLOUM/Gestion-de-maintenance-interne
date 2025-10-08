# Guide de Compréhension du Backend - Système de Maintenance

## Table des matières
1. [Architecture Générale](#architecture-générale)
2. [Les Modèles (Models)](#les-modèles)
3. [Les Controllers](#les-controllers)
4. [Les Services](#les-services)
5. [Le Système SLA](#le-système-sla)
6. [Les Notifications](#les-notifications)
7. [Les Rapports PDF](#les-rapports-pdf)
8. [Flux de Données](#flux-de-données)

---

## Architecture Générale

### Structure Laravel MVC + Inertia.js

```
Requête HTTP
    ↓
Route (routes/web.php)
    ↓
Middleware (auth, permissions)
    ↓
Controller (app/Http/Controllers/)
    ↓
Service/Model (logique métier)
    ↓
Response Inertia (JSON → Vue.js)
```

### Dossiers principaux

```
app/
├── Http/Controllers/      # Traite les requêtes HTTP
├── Models/               # Représente les tables de la base de données
├── Services/             # Logique métier réutilisable
├── Notifications/        # Système de notifications
├── Policies/             # Autorisations (qui peut faire quoi)
└── Providers/            # Configuration des services

routes/
└── web.php               # Définition des routes

database/
├── migrations/           # Structure de la base de données
└── seeders/             # Données initiales

resources/
├── js/Pages/            # Pages Vue.js (frontend)
└── views/reports/       # Templates Blade pour PDF
```

---

## Les Modèles

Les modèles représentent les tables de la base de données. Chaque modèle correspond à une table.

### 1. **User.php** (Utilisateurs)

**Rôles disponibles :**
- `is_admin` : Administrateur (accès complet)
- `is_responsable_it` : Responsable IT (gestion des tickets)
- `is_technicien` : Technicien (résolution des tickets)
- Employé : Utilisateur normal (création de tickets)

**Relations :**
```php
// Tickets créés par l'utilisateur
requestedTickets() → hasMany(Ticket, 'requester_id')

// Tickets assignés au technicien
assignedTickets() → hasMany(Ticket, 'assigned_to')

// Service de l'utilisateur
service() → belongsTo(Service)
```

### 2. **Ticket.php** (Tickets de maintenance)

**Statuts possibles :**
- `pending` : En attente d'assignation
- `in_progress` : En cours de traitement
- `resolved` : Résolu (en attente de validation)
- `validated` : Validé par le demandeur
- `closed` : Fermé automatiquement
- `cancelled` : Annulé

**Priorités :**
- `low` : Basse
- `medium` : Moyenne
- `high` : Haute
- `urgent` : Urgente

**Relations :**
```php
requester() → belongsTo(User, 'requester_id')      // Demandeur
assignedUser() → belongsTo(User, 'assigned_to')    // Technicien assigné
category() → belongsTo(Category)                    // Catégorie
service() → belongsTo(Service)                      // Service
comments() → hasMany(TicketComment)                 // Commentaires
attachments() → hasMany(TicketAttachment)           // Pièces jointes
histories() → hasMany(TicketHistory)                // Historique
evaluation() → hasOne(Evaluation)                   // Évaluation
```

**Calculs SLA :**
```php
// Temps de résolution (en minutes)
resolution_time = resolved_at - created_at

// Temps de première réponse (en minutes)
first_response_time = premier commentaire ou assignation - created_at

// Respect du SLA
is_sla_breached = true/false (basé sur les limites par priorité)
```

### 3. **Evaluation.php** (Évaluations)

Les évaluations permettent aux demandeurs de noter la qualité du service.

```php
rating: 1-5 étoiles
comment: Commentaire optionnel
ticket() → belongsTo(Ticket)
```

### 4. **TicketHistory.php** (Historique)

Enregistre toutes les modifications des tickets.

**Types d'actions :**
- `created` : Ticket créé
- `assigned` : Assigné à un technicien
- `status_changed` : Changement de statut
- `priority_changed` : Changement de priorité
- `commented` : Commentaire ajouté
- `resolved` : Marqué comme résolu
- `validated` : Validé par le demandeur
- `reopened` : Réouvert
- `cancelled` : Annulé

---

## Les Controllers

### 1. **TicketController.php** (Gestion des tickets)

**Méthodes principales :**

```php
index()
// Liste tous les tickets (avec filtres et permissions)
// Filtre automatique selon le rôle :
// - Employé : voit uniquement ses tickets
// - Technicien : voit ses tickets assignés
// - Responsable IT : voit tous les tickets

store(Request $request)
// Créer un nouveau ticket
// 1. Validation des données
// 2. Upload des pièces jointes
// 3. Génération du ticket_number
// 4. Création du ticket
// 5. Notification au Responsable IT

show($id)
// Afficher les détails d'un ticket
// Charge : commentaires, pièces jointes, historique, évaluation

update(Request $request, $id)
// Modifier un ticket (titre, description, priorité)
// Enregistre les modifications dans l'historique

assign(Request $request, $id)
// Assigner un ticket à un technicien
// 1. Vérifie que le technicien existe
// 2. Change le statut à 'in_progress'
// 3. Enregistre dans l'historique
// 4. Notifie le technicien

changeStatus(Request $request, $id)
// Changer le statut d'un ticket
// Utilise TicketService pour valider les transitions

addComment(Request $request, $id)
// Ajouter un commentaire
// Notifie les parties concernées
```

### 2. **DashboardController.php** (Tableaux de bord)

Génère les statistiques pour chaque rôle.

```php
employe()
// Dashboard employé
// - Mes tickets (statuts, priorités)
// - Tickets récents
// - Statistiques personnelles

technicien()
// Dashboard technicien
// - Tickets assignés (groupés par statut)
// - Tickets urgents
// - Performance SLA
// - Tickets résolus ce mois

responsableIt()
// Dashboard Responsable IT
// - Vue d'ensemble complète
// - Tickets par statut, priorité, service
// - Tickets non assignés
// - Violations SLA
// - Statistiques par technicien

direction()
// Dashboard Direction
// - KPIs globaux
// - Répartition par service
// - Répartition par catégorie
// - Tendances mensuelles
// - Taux de satisfaction
```

### 3. **ReportController.php** (Rapports PDF)

Génère les rapports d'export en PDF ou Excel.

```php
generate(Request $request)
// Génère un rapport selon le type demandé
// Types : general, detailed, technician, service

slaReport(Request $request)
// Génère le rapport SLA
// - Violations SLA par priorité
// - Performance par technicien
// - Tendances temporelles

// Flux :
// 1. Récupère les données filtrées par date
// 2. Calcule les statistiques
// 3. Génère la vue Blade (resources/views/reports/)
// 4. Convertit en PDF avec DomPDF ou Excel avec Maatwebsite
```

### 4. **EvaluationController.php** (Évaluations)

```php
store(Request $request, $ticketId)
// Enregistrer une évaluation
// 1. Vérifie que le ticket est validé
// 2. Vérifie qu'il n'y a pas déjà d'évaluation
// 3. Enregistre l'évaluation
// 4. Notifie le Responsable IT et le technicien
```

---

## Les Services

### **TicketService.php** (Logique métier des tickets)

Centralise la logique complexe pour éviter de dupliquer le code.

```php
// Machine à états : définit les transitions autorisées
protected $allowedTransitions = [
    'pending' => ['in_progress', 'cancelled'],
    'in_progress' => ['resolved', 'cancelled'],
    'resolved' => ['validated', 'in_progress'],
    'validated' => ['closed'],
    'cancelled' => ['pending'],  // Permet de réouvrir
];

canTransition($currentStatus, $newStatus)
// Vérifie si une transition de statut est autorisée

calculateSLA($ticket)
// Calcule les métriques SLA
// - resolution_time : temps total de résolution
// - first_response_time : temps avant première réponse
// - is_sla_breached : violation du SLA selon la priorité

getSLALimits($priority)
// Retourne les limites SLA selon la priorité
// Urgent: résolution 4h, réponse 30min
// High: résolution 24h, réponse 2h
// Medium: résolution 48h, réponse 4h
// Low: résolution 72h, réponse 8h
```

---

## Le Système SLA (Service Level Agreement)

### Principe

Le SLA définit les délais maximum pour :
1. **Première réponse** : Temps avant qu'un technicien ne réponde
2. **Résolution** : Temps total pour résoudre le ticket

### Limites par priorité

| Priorité | Résolution | Première réponse |
|----------|-----------|------------------|
| Urgent   | 4 heures  | 30 minutes       |
| High     | 24 heures | 2 heures         |
| Medium   | 48 heures | 4 heures         |
| Low      | 72 heures | 8 heures         |

### Calcul automatique

```php
// Dans TicketService.php
public function calculateSLA($ticket)
{
    // 1. Temps de résolution
    $resolutionTime = $ticket->resolved_at
        ? $ticket->created_at->diffInMinutes($ticket->resolved_at)
        : null;

    // 2. Temps de première réponse
    $firstResponse = $ticket->histories()
        ->whereIn('action', ['assigned', 'commented'])
        ->oldest()
        ->first();

    $firstResponseTime = $firstResponse
        ? $ticket->created_at->diffInMinutes($firstResponse->created_at)
        : null;

    // 3. Vérification du respect du SLA
    $limits = $this->getSLALimits($ticket->priority);
    $isSlaBreached = false;

    if ($resolutionTime && $resolutionTime > $limits['resolution']) {
        $isSlaBreached = true;
    }
    if ($firstResponseTime && $firstResponseTime > $limits['first_response']) {
        $isSlaBreached = true;
    }

    return [
        'resolution_time' => $resolutionTime,
        'first_response_time' => $firstResponseTime,
        'is_sla_breached' => $isSlaBreached,
        'limits' => $limits,
    ];
}
```

---

## Les Notifications

### Types de notifications

1. **TicketCreated** : Nouveau ticket créé
   - Destinataire : Responsable IT
   - Contenu : Numéro du ticket, priorité, demandeur

2. **TicketAssigned** : Ticket assigné
   - Destinataire : Technicien assigné
   - Contenu : Numéro du ticket, priorité, délai SLA

3. **TicketStatusChanged** : Statut modifié
   - Destinataires : Demandeur + technicien
   - Contenu : Nouveau statut, détails

4. **TicketCommented** : Nouveau commentaire
   - Destinataires : Demandeur + technicien
   - Contenu : Auteur du commentaire, extrait

5. **TicketEvaluated** : Ticket évalué
   - Destinataires : Responsable IT + technicien
   - Contenu : Note (étoiles), niveau de satisfaction

### Fonctionnement

```php
// Exemple dans TicketController.php
use App\Notifications\TicketCreated;
use Illuminate\Support\Facades\Notification;

// Créer le ticket
$ticket = Ticket::create($data);

// Notifier le Responsable IT
$responsableIt = User::where('is_responsable_it', true)->first();
if ($responsableIt) {
    $responsableIt->notify(new TicketCreated($ticket));
}
```

### Stockage

Les notifications sont stockées dans la table `notifications` :
```
id, type, notifiable_type, notifiable_id, data, read_at, created_at
```

---

## Les Rapports PDF

### Structure

Les rapports utilisent **Blade templates** + **DomPDF**.

```
resources/views/reports/
├── summary.blade.php      # Rapport général
├── detailed.blade.php     # Rapport détaillé
├── technician.blade.php   # Rapport par technicien
├── service.blade.php      # Rapport par service
└── sla.blade.php         # Rapport SLA
```

### Génération d'un PDF

```php
// Dans ReportController.php
use Barryvdh\DomPDF\Facade\Pdf;

public function generate(Request $request)
{
    // 1. Récupérer les données
    $tickets = Ticket::whereBetween('created_at', [$dateFrom, $dateTo])
        ->with(['requester', 'assignedUser', 'category'])
        ->get();

    // 2. Calculer les statistiques
    $stats = [
        'total' => $tickets->count(),
        'resolved' => $tickets->where('status', 'resolved')->count(),
        // ...
    ];

    // 3. Générer la vue Blade
    $pdf = Pdf::loadView('reports.summary', [
        'tickets' => $tickets,
        'stats' => $stats,
        'period' => ['from' => $dateFrom, 'to' => $dateTo],
    ]);

    // 4. Retourner le PDF
    return $pdf->download('rapport-' . now()->format('Y-m-d') . '.pdf');
}
```

### Styles PDF

Les templates Blade utilisent du CSS inline car DomPDF ne supporte pas les CSS externes.

```blade
<style>
    body { font-family: DejaVu Sans, sans-serif; }
    .header { background: #4f46e5; color: white; padding: 20px; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; }
</style>
```

---

## Flux de Données

### Exemple complet : Création d'un ticket

```
1. FRONTEND (Create.vue)
   - L'employé remplit le formulaire
   - Clique sur "Créer le ticket"
   - router.post(route('tickets.store'), form)

2. ROUTE (routes/web.php)
   - Route::post('/tickets', [TicketController::class, 'store'])
   - Middleware: auth, verified

3. CONTROLLER (TicketController.php)
   - Validation des données
   - Upload des pièces jointes
   - Génération du ticket_number

4. MODEL (Ticket.php)
   - $ticket = Ticket::create($validatedData)
   - Sauvegarde dans la base de données

5. NOTIFICATION
   - TicketCreated envoyée au Responsable IT

6. RESPONSE
   - Inertia::render('Tickets/Index')
   - Message de succès affiché

7. FRONTEND
   - Redirection vers la liste des tickets
   - Notification de succès affichée
```

### Exemple : Assignation d'un ticket

```
1. FRONTEND (ResponsableIt.vue)
   - Le Responsable IT clique sur "Assigner"
   - Sélectionne un technicien
   - router.post(route('tickets.assign'), { assigned_to: techId })

2. CONTROLLER (TicketController.php)
   - assign(Request $request, $id)
   - Vérifie que le technicien existe

3. SERVICE (TicketService.php)
   - canTransition('pending', 'in_progress') → true
   - Autorise le changement de statut

4. MODEL (Ticket.php)
   - $ticket->update([
       'assigned_to' => $techId,
       'status' => 'in_progress'
     ])

5. HISTORY (TicketHistory.php)
   - TicketHistory::create([
       'action' => 'assigned',
       'user_id' => auth()->id(),
       'details' => "Assigné à {$tech->name}"
     ])

6. NOTIFICATION
   - TicketAssigned envoyée au technicien

7. RESPONSE
   - Message de succès
   - Dashboard mis à jour automatiquement (Inertia)
```

---

## Points Clés à Retenir

### 1. **Sécurité et Permissions**

Chaque action vérifie les permissions :
```php
// Dans TicketController
if (!auth()->user()->is_responsable_it) {
    abort(403, 'Action non autorisée');
}
```

### 2. **Machine à états (Status Transitions)**

Toutes les transitions de statut passent par `TicketService::canTransition()` pour garantir la cohérence.

### 3. **Historique complet**

Chaque modification est enregistrée dans `TicketHistory` pour la traçabilité.

### 4. **Notifications en temps réel**

Toutes les actions importantes génèrent des notifications pour tenir les utilisateurs informés.

### 5. **Calcul SLA automatique**

Le SLA est calculé automatiquement et vérifié pour chaque ticket résolu.

---

## Commandes Utiles

```bash
# Voir les routes
php artisan route:list

# Voir les modèles et leurs relations
php artisan model:show Ticket

# Créer un contrôleur
php artisan make:controller NomController

# Créer un modèle avec migration
php artisan make:model NomModele -m

# Créer une notification
php artisan make:notification NomNotification

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Prochaines Étapes pour Apprendre

1. **Lire les modèles** : Commencez par [Ticket.php](app/Models/Ticket.php) pour comprendre les relations
2. **Étudier un controller** : Lisez [TicketController.php](app/Http/Controllers/TicketController.php) méthode par méthode
3. **Suivre un flux complet** : Tracez le parcours d'une création de ticket du frontend au backend
4. **Comprendre le SLA** : Étudiez [TicketService.php](app/Services/TicketService.php)
5. **Explorer les notifications** : Regardez [app/Notifications/](app/Notifications/)
6. **Analyser un rapport PDF** : Ouvrez [resources/views/reports/sla.blade.php](resources/views/reports/sla.blade.php)

---

**Besoin d'aide ?** Demandez-moi d'expliquer une section spécifique en détail !
