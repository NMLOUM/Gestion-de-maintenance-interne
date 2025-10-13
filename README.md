# 🔧 Système de Gestion de Maintenance Interne

Application web complète pour la gestion des tickets de maintenance avec suivi SLA, notifications en temps réel et tableaux de bord interactifs.

## 📸 Aperçu

Une solution moderne construite avec Laravel 10, Vue 3 (Composition API) et Inertia.js pour gérer efficacement les demandes de maintenance dans votre organisation.

## ✨ Fonctionnalités Principales

### 👥 Pour les Employés
- ✅ Création de tickets de maintenance avec pièces jointes
- 📊 Dashboard personnel avec KPI colorés (couleurs pastel douces)
- 🔔 Suivi en temps réel de l'état des tickets
- 💬 Ajout de commentaires et échanges avec les techniciens
- ⭐ Évaluation des interventions terminées
- 📱 Notifications instantanées (email + base de données)
- ❓ Section Aide & FAQ intégrée

### 🔧 Pour les Techniciens
- 📋 Vue des tickets assignés avec badges de priorité
- ⏱️ Gestion du temps passé sur les interventions
- 🚀 Actions rapides : Démarrer, Résoudre, Commenter
- 📎 Accès aux pièces jointes des tickets
- 💡 Notes de résolution détaillées
- 🎯 Dashboard avec statistiques personnelles

### 📊 Pour le Responsable IT
- 🎛️ Assignation intelligente des tickets aux techniciens
- 📈 KPI avancés : Tickets en retard, taux de résolution, charge de travail
- 🔍 Vue globale de tous les tickets du service IT
- ⚠️ Alertes SLA et tickets en retard
- 📑 Génération de rapports PDF/Excel
- 👥 Gestion des évaluations

### 🏢 Pour la Direction
- 📊 Rapports consolidés et statistiques globales
- 📈 Analyse des performances par service et technicien
- 📥 Export PDF et Excel avec filtres avancés
- 🎯 Vue d'ensemble des KPI organisationnels
- 🔍 Accès en lecture à tous les tickets

## 🎨 Améliorations UI/UX Récentes

### Design Moderne
- 🎨 **KPI avec couleurs pastel** (bleu, ambre, orange, émeraude)
- 💫 **Animations fluides** : hover, scale, rotation
- 🃏 **Cartes d'actions rapides** avec effets visuels attractifs
- 🎭 **SweetAlert2** personnalisés pour chaque action
- 📐 **Layout optimisé** avec largeurs adaptées

### Feedback Utilisateur
- ✅ SweetAlert sans auto-close (contrôle utilisateur)
- 🔔 Notifications enrichies avec commentaires de résolution
- ⏱️ Affichage du temps passé dans les notifications
- 🎯 Messages contextuels selon le rôle utilisateur

## 🛠️ Technologies Utilisées

### Backend
- **Laravel 10** - Framework PHP moderne
- **MySQL** - Base de données relationnelle
- **Laravel Policies** - Gestion des permissions
- **Laravel Notifications** - Système de notifications multi-canal
- **Maatwebsite Excel** - Export Excel
- **Barryvdh DomPDF** - Génération de PDF

### Frontend
- **Vue 3** (Composition API avec `<script setup>`)
- **Inertia.js** - Pont Laravel-Vue sans API
- **Tailwind CSS** - Framework CSS utilitaire
- **SweetAlert2** - Alertes élégantes
- **Vite** - Build tool moderne et rapide

## 📋 Prérequis

- PHP 8.1 ou supérieur
- MySQL 5.7 ou supérieur
- Composer
- Node.js 16+ et NPM
- Extensions PHP : `ext-zip`, `ext-dom`, `ext-curl`, `ext-gd`, `ext-mbstring`

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/NMLOUM/Gestion-de-maintenance-interne.git
cd Gestion-de-maintenance-interne
```

### 2. Installer les dépendances

```bash
# Dépendances PHP
composer install

# Dépendances Node.js
npm install
```

### 3. Configuration de l'environnement

```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 4. Configuration de la base de données

Modifier le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maintenance_system
DB_USERNAME=votre_username
DB_PASSWORD=votre_password
```

### 5. Configuration des emails (optionnel)

```env
MAIL_MAILER=smtp
MAIL_HOST=votre-serveur-smtp
MAIL_PORT=587
MAIL_USERNAME=votre-email
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@maintenance.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Migrations et données de test

```bash
# Exécuter les migrations
php artisan migrate

# Peupler avec des données de test
php artisan db:seed
```

### 7. Configuration du stockage

```bash
# Créer le lien symbolique pour le storage
php artisan storage:link
```

### 8. Compiler les assets

```bash
# Pour le développement (avec hot reload)
npm run dev

# Pour la production
npm run build
```

### 9. Démarrer l'application

```bash
# Serveur de développement Laravel
php artisan serve

# L'application sera accessible sur http://localhost:8000
```

## 👤 Comptes de Test

Après l'installation, connectez-vous avec :

| Rôle | Email | Mot de passe | Description |
|------|-------|--------------|-------------|
| **Direction** | direction@maintenance.local | password | Accès complet, rapports globaux |
| **Responsable IT** | responsable.it@maintenance.local | password | Gestion des tickets IT, assignation |
| **Technicien** | tech.informatique@maintenance.local | password | Résolution des tickets assignés |
| **Employé** | i.sarr@entreprise.sn | password | Création et suivi de tickets |

## 📁 Structure du Projet

```
maintenance-system/
├── app/
│   ├── Http/Controllers/     # Contrôleurs
│   ├── Models/               # Modèles Eloquent
│   ├── Policies/             # Policies d'autorisation
│   ├── Services/             # Services métier (SLA, Notifications)
│   └── Mail/                 # Classes d'emails
├── database/
│   ├── migrations/           # Migrations de la base de données
│   └── seeders/              # Seeders pour données de test
├── resources/
│   ├── js/
│   │   ├── Pages/           # Pages Vue (Dashboard, Tickets, etc.)
│   │   ├── Components/      # Composants réutilisables
│   │   └── Layouts/         # Layouts de page
│   └── views/               # Templates Blade (PDF, emails)
└── routes/
    └── web.php              # Routes de l'application
```

## 🔐 Système de Permissions

### Matrice des Autorisations

| Action | Employé | Technicien | Responsable IT | Direction |
|--------|---------|------------|----------------|-----------|
| Créer un ticket | ✅ | ✅ | ✅ | ✅ |
| Voir ses tickets | ✅ | - | - | - |
| Voir tickets assignés | - | ✅ | - | - |
| Assigner des tickets | - | - | ✅ | ✅ |
| Résoudre un ticket | - | ✅ | ✅ | ✅ |
| Annuler un ticket | - | - | ✅ | ✅ |
| Générer des rapports | - | - | ✅ | ✅ |
| Gérer les utilisateurs | - | - | - | ✅ |

## 📊 Système SLA (Service Level Agreement)

### Délais selon la Priorité

| Priorité | Délai | Description |
|----------|-------|-------------|
| 🔴 **Critique** | 24h | Système en panne, production arrêtée |
| 🟠 **Élevée** | 3 jours | Impact significatif sur le travail |
| 🟡 **Normale** | 7 jours | Fonctionnalité dégradée |
| 🟢 **Faible** | 14 jours | Amélioration ou demande mineure |

### Statuts des Tickets

- **Pending** (En attente) - Ticket créé, en attente d'assignation
- **In Progress** (En cours) - Technicien travaille dessus
- **Resolved** (Résolu) - Solution apportée, en attente de validation
- **Closed** (Fermé) - Validé et clôturé par l'employé
- **Cancelled** (Annulé) - Annulé par le responsable ou l'admin

## 🔔 Système de Notifications

### Types de Notifications

1. **Création de ticket** → Responsable IT notifié
2. **Assignation** → Technicien notifié
3. **Changement de statut** → Demandeur notifié
4. **Résolution** → Demandeur notifié avec détails (temps + commentaire)
5. **Commentaire ajouté** → Parties concernées notifiées
6. **Évaluation** → Responsable IT notifié

### Canaux de Notification

- 📧 **Email** (Laravel Mail)
- 💾 **Base de données** (cloche de notifications)
- 🔔 **Badge en temps réel** sur l'interface

## 📈 API Routes Principales

```php
// Authentification
POST   /login                    # Connexion
POST   /logout                   # Déconnexion
POST   /register                 # Inscription (si activé)

// Tickets
GET    /tickets                  # Liste des tickets
POST   /tickets                  # Créer un ticket
GET    /tickets/{id}             # Détails d'un ticket
PUT    /tickets/{id}             # Modifier un ticket
POST   /tickets/{id}/status      # Changer le statut
POST   /tickets/{id}/assign      # Assigner à un technicien
POST   /tickets/{id}/comments    # Ajouter un commentaire
POST   /tickets/{id}/attachments # Ajouter une pièce jointe
POST   /tickets/{id}/evaluate    # Évaluer l'intervention

// Rapports (Responsable IT / Direction uniquement)
GET    /reports                  # Page des rapports
GET    /reports/export-pdf       # Export PDF
GET    /reports/export-excel     # Export Excel

// Notifications
GET    /notifications            # Liste des notifications
POST   /notifications/{id}/read  # Marquer comme lu
POST   /notifications/read-all   # Tout marquer comme lu
```

## 🎯 Scénario d'Utilisation Typique

1. **Employé** crée un ticket → Enregistré en BD
2. **Responsable IT** reçoit une notification
3. Il consulte son dashboard
4. Il **assigne le ticket** à un technicien disponible
5. **Technicien** reçoit une notification
6. Il voit le ticket dans son dashboard
7. Il clique sur **"Démarrer l'intervention"**
8. Il travaille sur le problème
9. Il clique sur **"Résoudre"**, ajoute un commentaire et le temps passé
10. **Employé** reçoit une notification de résolution
11. Il consulte le ticket résolu
12. Il **évalue l'intervention** (note + commentaire)
13. **Responsable IT** consulte les statistiques et rapports

## 🛠️ Commandes Artisan Utiles

```bash
# Nettoyer tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Notifier les tickets en retard (via CRON)
php artisan tickets:notify-overdue

# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Générer un nouveau controller
php artisan make:controller NomController

# Créer une migration
php artisan make:migration nom_migration

# Créer un modèle avec migration
php artisan make:model NomModele -m
```

## ⚙️ Configuration CRON (Production)

```bash
# Ajouter dans crontab (crontab -e)
* * * * * cd /chemin/vers/projet && php artisan schedule:run >> /dev/null 2>&1

# Ou spécifiquement pour les notifications de tickets en retard
0 9 * * * cd /chemin/vers/projet && php artisan tickets:notify-overdue
```

## 🐛 Dépannage

### Erreur "ERR_CONNECTION_REFUSED" dans la console

**Cause** : Le serveur de développement Vite (`npm run dev`) n'est pas lancé.

**Solution** :
- Si vous utilisez `npm run build`, c'est normal, ignorez ces erreurs
- Si vous développez, lancez `npm run dev` dans un terminal séparé

### Erreur "Class not found"

```bash
composer dump-autoload
```

### Erreur de permissions sur storage/

```bash
chmod -R 775 storage bootstrap/cache
```

### Problème avec les assets

```bash
npm run build
php artisan cache:clear
```

## 📝 Personnalisation

### Ajouter une nouvelle catégorie

```sql
INSERT INTO categories (name, description) VALUES ('Électricité', 'Problèmes électriques');
```

### Modifier les priorités SLA

Éditez `config/sla.php` ou la migration `create_tickets_table.php`.

### Ajouter un nouveau rôle

1. Modifier l'enum `role` dans la migration des users
2. Mettre à jour `TicketPolicy.php`
3. Adapter les dashboards et interfaces

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créez une branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Auteur

**NMLOUM**
- GitHub: [@NMLOUM](https://github.com/NMLOUM)
- Email: ndeyemaguetteloum71@gmail.com

## 🙏 Remerciements

- Laravel Team pour le framework exceptionnel
- Vue.js Team pour Vue 3
- Tailwind CSS pour le framework CSS
- La communauté open source

---

**Développé avec ❤️ et Laravel 10**

🤖 *Améliorations assistées par [Claude Code](https://claude.com/claude-code)*
