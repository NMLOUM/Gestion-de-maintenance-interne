<?php
// database/seeders/TicketsSeeder.php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Service;
use App\Models\Category;
use App\Models\TicketHistory;
use App\Models\TicketComment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TicketsSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'employe')->get();
        $technicians = User::where('role', 'technicien')->get();
        $services = Service::all();
        $categories = Category::all();

        $ticketTemplates = [
            [
                'title' => 'Ordinateur qui ne démarre plus',
                'description' => 'Mon ordinateur portable ne s\'allume plus depuis ce matin. Le voyant d\'alimentation ne s\'allume pas non plus.',
                'category' => 'IT',
                'priority' => 'high',
                'location' => 'Bureau 204',
            ],
            [
                'title' => 'Climatisation en panne',
                'description' => 'La climatisation de la salle de réunion principale ne fonctionne plus. Il fait très chaud et cela devient inconfortable pour les réunions.',
                'category' => 'CLIM',
                'priority' => 'high',
                'location' => 'Salle de réunion A',
            ],
            [
                'title' => 'Chaise de bureau cassée',
                'description' => 'Le dossier de ma chaise de bureau s\'est cassé. Je ne peux plus l\'incliner et cela me fait mal au dos.',
                'category' => 'MOB',
                'priority' => 'normal',
                'location' => 'Bureau 156',
            ],
            [
                'title' => 'Prise électrique défaillante',
                'description' => 'La prise électrique près de mon bureau ne fonctionne plus. J\'ai testé avec plusieurs appareils.',
                'category' => 'ELEC',
                'priority' => 'normal',
                'location' => 'Bureau 312',
            ],
            [
                'title' => 'Fuite d\'eau dans les toilettes',
                'description' => 'Il y a une fuite d\'eau au niveau du robinet des toilettes du 2ème étage. L\'eau coule en permanence.',
                'category' => 'BAT',
                'priority' => 'high',
                'location' => 'Toilettes 2ème étage',
            ],
            [
                'title' => 'Problème de connexion internet',
                'description' => 'Je n\'arrive plus à me connecter à internet depuis mon poste. Le wifi ne fonctionne pas non plus.',
                'category' => 'IT',
                'priority' => 'critical',
                'location' => 'Bureau 178',
            ],
            [
                'title' => 'Éclairage défaillant',
                'description' => 'Plusieurs néons du couloir principal clignotent et certains ne s\'allument plus du tout.',
                'category' => 'ELEC',
                'priority' => 'normal',
                'location' => 'Couloir principal',
            ],
            [
                'title' => 'Machine à café en panne',
                'description' => 'La machine à café de l\'espace détente ne fonctionne plus. Elle affiche un message d\'erreur.',
                'category' => 'MACH',
                'priority' => 'low',
                'location' => 'Espace détente',
            ],
            [
                'title' => 'Téléphone qui ne sonne plus',
                'description' => 'Mon téléphone de bureau ne sonne plus quand on m\'appelle. Je peux appeler mais pas recevoir.',
                'category' => 'TEL',
                'priority' => 'normal',
                'location' => 'Bureau 245',
            ],
            [
                'title' => 'Porte d\'entrée difficile à ouvrir',
                'description' => 'La porte d\'entrée principale est très difficile à ouvrir. Il faut forcer pour l\'ouvrir.',
                'category' => 'BAT',
                'priority' => 'normal',
                'location' => 'Entrée principale',
            ],
            [
                'title' => 'Imprimante qui bourre',
                'description' => 'L\'imprimante partagée du service bourre constamment. Les feuilles se coincent à chaque impression.',
                'category' => 'IT',
                'priority' => 'normal',
                'location' => 'Espace imprimantes',
            ]
        ];

        foreach ($ticketTemplates as $index => $template) {
            $requester = $users->random();
            $category = $categories->where('code', $template['category'])->first();
            
            // Dates variées (derniers 30 jours)
            $createdAt = Carbon::now()->subDays(rand(1, 30));
            
            $ticket = Ticket::create([
                'title' => $template['title'],
                'description' => $template['description'],
                'requester_id' => $requester->id,
                'service_id' => $requester->service_id,
                'category_id' => $category->id,
                'priority' => $template['priority'],
                'location' => $template['location'],
                'status' => $this->getRandomStatus(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Ajouter l'historique de création
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $requester->id,
                'action' => 'created',
                'description' => 'Ticket créé par ' . $requester->name,
                'created_at' => $createdAt,
            ]);

            // Assigner aléatoirement certains tickets
            if (rand(1, 100) > 30) { // 70% de chance d'être assigné
                $technician = $technicians->random();
                $assignedAt = $createdAt->copy()->addHours(rand(1, 8));
                
                $ticket->update([
                    'assigned_to' => $technician->id,
                    'assigned_at' => $assignedAt,
                    'updated_at' => $assignedAt,
                ]);

                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $technician->id,
                    'action' => 'assigned',
                    'description' => 'Ticket assigné à ' . $technician->name,
                    'created_at' => $assignedAt,
                    'old_values' => ['assigned_to' => null],
                    'new_values' => ['assigned_to' => $technician->id],
                ]);
            }

            // Ajouter des états plus avancés pour certains tickets
            if ($ticket->assigned_to && rand(1, 100) > 40) { // 60% des tickets assignés
                $this->progressTicket($ticket, $technician ?? $technicians->random());
            }

            // Ajouter des commentaires aléatoires
            if (rand(1, 100) > 50) { // 50% de chance d'avoir des commentaires
                $this->addRandomComments($ticket, $requester, $ticket->assignedUser);
            }
        }
    }

    private function getRandomStatus()
    {
        $statuses = ['pending', 'pending', 'in_progress', 'resolved', 'closed'];
        return $statuses[array_rand($statuses)];
    }

    private function progressTicket($ticket, $technician)
    {
        $currentTime = $ticket->assigned_at->copy()->addHours(rand(1, 24));

        // Démarrer le ticket
        if (rand(1, 100) > 30) { // 70% de chance d'être démarré
            $ticket->update([
                'status' => 'in_progress',
                'started_at' => $currentTime,
                'updated_at' => $currentTime,
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $technician->id,
                'action' => 'status_changed',
                'description' => 'Statut changé de "En attente" à "En cours" par ' . $technician->name,
                'created_at' => $currentTime,
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'in_progress'],
            ]);

            $currentTime = $currentTime->addHours(rand(2, 48));

            // Résoudre le ticket
            if (rand(1, 100) > 40) { // 60% de chance d'être résolu
                $actualHours = rand(1, 8);
                $ticket->update([
                    'status' => 'resolved',
                    'resolved_at' => $currentTime,
                    'actual_hours' => $actualHours,
                    'updated_at' => $currentTime,
                ]);

                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $technician->id,
                    'action' => 'status_changed',
                    'description' => 'Statut changé de "En cours" à "Résolu" par ' . $technician->name,
                    'created_at' => $currentTime,
                    'old_values' => ['status' => 'in_progress'],
                    'new_values' => ['status' => 'resolved'],
                ]);

                // Fermer le ticket
                if (rand(1, 100) > 60) { // 40% de chance d'être fermé
                    $currentTime = $currentTime->addHours(rand(4, 72));
                    $ticket->update([
                        'status' => 'closed',
                        'closed_at' => $currentTime,
                        'updated_at' => $currentTime,
                    ]);

                    TicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $ticket->requester_id,
                        'action' => 'status_changed',
                        'description' => 'Ticket fermé par ' . $ticket->requester->name,
                        'created_at' => $currentTime,
                        'old_values' => ['status' => 'resolved'],
                        'new_values' => ['status' => 'closed'],
                    ]);
                }
            }
        }
    }

    private function addRandomComments($ticket, $requester, $technician)
    {
        $comments = [
            [
                'user' => $requester,
                'text' => 'Merci de prendre en charge ma demande rapidement.',
                'is_internal' => false,
            ],
            [
                'user' => $technician,
                'text' => 'J\'ai vérifié le problème, intervention prévue demain matin.',
                'is_internal' => false,
            ],
            [
                'user' => $technician,
                'text' => 'Pièce de rechange commandée, délai 2-3 jours.',
                'is_internal' => true,
            ],
            [
                'user' => $requester,
                'text' => 'Le problème semble persister, pouvez-vous revenir vérifier ?',
                'is_internal' => false,
            ],
            [
                'user' => $technician,
                'text' => 'Intervention terminée, tout fonctionne normalement.',
                'is_internal' => false,
            ]
        ];

        $numComments = rand(1, 3);
        $selectedComments = array_slice($comments, 0, $numComments);

        foreach ($selectedComments as $index => $commentData) {
            if (!$commentData['user']) continue;

            $commentTime = $ticket->created_at->copy()->addHours(rand(1, 48 * ($index + 1)));

            $comment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $commentData['user']->id,
                'comment' => $commentData['text'],
                'is_internal' => $commentData['is_internal'],
                'created_at' => $commentTime,
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $commentData['user']->id,
                'action' => 'comment_added',
                'description' => ($commentData['is_internal'] ? 'Commentaire interne' : 'Commentaire') . ' ajouté par ' . $commentData['user']->name,
                'created_at' => $commentTime,
            ]);
        }
    }
}
