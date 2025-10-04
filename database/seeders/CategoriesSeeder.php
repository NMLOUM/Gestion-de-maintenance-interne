<?php
// database/seeders/CategoriesSeeder.php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Informatique',
                'code' => 'IT',
                'color' => '#3B82F6',
                'description' => 'Problèmes liés au matériel informatique, logiciels, réseau',
                'is_active' => true,
            ],
            [
                'name' => 'Bâtiment',
                'code' => 'BAT',
                'color' => '#F59E0B',
                'description' => 'Problèmes de bâtiment, plomberie, électricité générale',
                'is_active' => true,
            ],
            [
                'name' => 'Mobilier',
                'code' => 'MOB',
                'color' => '#10B981',
                'description' => 'Mobilier de bureau, chaises, tables, rangements',
                'is_active' => true,
            ],
            [
                'name' => 'Climatisation',
                'code' => 'CLIM',
                'color' => '#06B6D4',
                'description' => 'Système de climatisation et ventilation',
                'is_active' => true,
            ],
            [
                'name' => 'Sécurité',
                'code' => 'SEC',
                'color' => '#EF4444',
                'description' => 'Systèmes de sécurité, alarmes, contrôle d\'accès',
                'is_active' => true,
            ],
            [
                'name' => 'Électricité',
                'code' => 'ELEC',
                'color' => '#F97316',
                'description' => 'Installation électrique, éclairage, prises',
                'is_active' => true,
            ],
            [
                'name' => 'Machines Production',
                'code' => 'MACH',
                'color' => '#8B5CF6',
                'description' => 'Machines et équipements de production',
                'is_active' => true,
            ],
            [
                'name' => 'Véhicules',
                'code' => 'VEH',
                'color' => '#6B7280',
                'description' => 'Véhicules de société et équipements mobiles',
                'is_active' => true,
            ],
            [
                'name' => 'Nettoyage',
                'code' => 'NET',
                'color' => '#84CC16',
                'description' => 'Nettoyage et entretien des locaux',
                'is_active' => true,
            ],
            [
                'name' => 'Téléphonie',
                'code' => 'TEL',
                'color' => '#EC4899',
                'description' => 'Téléphones, standards, télécommunications',
                'is_active' => true,
            ]
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['code' => $category['code']], // Critère de recherche
                $category // Données à créer si non trouvé
            );
        }
    }
}
