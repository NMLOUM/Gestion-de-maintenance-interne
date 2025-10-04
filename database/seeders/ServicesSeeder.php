<?php
// database/seeders/ServicesSeeder.php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $services = [
            [
                'name' => 'Direction Générale',
                'code' => 'DG',
                'description' => 'Direction générale et administration',
                'is_active' => true,
            ],
            [
                'name' => 'Ressources Humaines',
                'code' => 'RH',
                'description' => 'Gestion des ressources humaines',
                'is_active' => true,
            ],
            [
                'name' => 'Informatique',
                'code' => 'IT',
                'description' => 'Service informatique et technique',
                'is_active' => true,
            ],
            [
                'name' => 'Comptabilité',
                'code' => 'COMPTA',
                'description' => 'Service comptabilité et finance',
                'is_active' => true,
            ],
            [
                'name' => 'Commercial',
                'code' => 'COM',
                'description' => 'Service commercial et ventes',
                'is_active' => true,
            ],
            [
                'name' => 'Production',
                'code' => 'PROD',
                'description' => 'Service production et fabrication',
                'is_active' => true,
            ],
            [
                'name' => 'Maintenance',
                'code' => 'MAINT',
                'description' => 'Service maintenance et entretien',
                'is_active' => true,
            ],
            [
                'name' => 'Qualité',
                'code' => 'QUAL',
                'description' => 'Contrôle qualité et assurance',
                'is_active' => true,
            ]
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['code' => $service['code']], // Critère de recherche
                $service // Données à créer si non trouvé
            );
        }
    }
}

