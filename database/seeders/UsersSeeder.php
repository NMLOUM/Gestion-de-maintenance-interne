<?php
// database/seeders/UsersSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $services = Service::all();
        
        // Direction
        User::firstOrCreate(
            ['email' => 'direction@maintenance.local'],
            [
                'name' => 'Directeur Général',
                'email' => 'direction@maintenance.local',
                'password' => Hash::make('password'),
                'role' => 'direction',
                'service_id' => $services->where('code', 'DG')->first()->id,
                'phone' => '+221 77 123 45 67',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Responsable IT
        User::firstOrCreate(
            ['email' => 'responsable.it@maintenance.local'],
            [
                'name' => 'Mamadou Diallo',
                'email' => 'responsable.it@maintenance.local',
                'password' => Hash::make('password'),
                'role' => 'responsable_it',
                'service_id' => $services->where('code', 'IT')->first()->id,
                'phone' => '+221 77 234 56 78',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Techniciens
        $technicians = [
            [
                'name' => 'Amadou Ba',
                'email' => 'tech.informatique@maintenance.local',
                'service' => 'IT',
                'phone' => '+221 77 345 67 89',
            ],
            [
                'name' => 'Fatou Sall',
                'email' => 'tech.batiment@maintenance.local',
                'service' => 'MAINT',
                'phone' => '+221 77 456 78 90',
            ],
            [
                'name' => 'Ousmane Ndiaye',
                'email' => 'tech.electricite@maintenance.local',
                'service' => 'MAINT',
                'phone' => '+221 77 567 89 01',
            ],
            [
                'name' => 'Aïssatou Diouf',
                'email' => 'tech.climatisation@maintenance.local',
                'service' => 'MAINT',
                'phone' => '+221 77 678 90 12',
            ]
        ];

        foreach ($technicians as $tech) {
            User::firstOrCreate(
                ['email' => $tech['email']],
                [
                    'name' => $tech['name'],
                    'email' => $tech['email'],
                    'password' => Hash::make('password'),
                    'role' => 'technicien',
                    'service_id' => $services->where('code', $tech['service'])->first()->id,
                    'phone' => $tech['phone'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Employés par service
        $employees = [
            [
                'name' => 'Ibrahima Sarr',
                'email' => 'i.sarr@entreprise.sn',
                'service' => 'DG',
                'phone' => '+221 77 111 11 11',
            ],
            [
                'name' => 'Marième Faye',
                'email' => 'm.faye@entreprise.sn',
                'service' => 'RH',
                'phone' => '+221 77 222 22 22',
            ],
            [
                'name' => 'Cheikh Diop',
                'email' => 'c.diop@entreprise.sn',
                'service' => 'COMPTA',
                'phone' => '+221 77 333 33 33',
            ],
            [
                'name' => 'Ndeye Fall',
                'email' => 'n.fall@entreprise.sn',
                'service' => 'COM',
                'phone' => '+221 77 444 44 44',
            ],
            [
                'name' => 'Moussa Kane',
                'email' => 'm.kane@entreprise.sn',
                'service' => 'PROD',
                'phone' => '+221 77 555 55 55',
            ],
            [
                'name' => 'Bineta Sow',
                'email' => 'b.sow@entreprise.sn',
                'service' => 'QUAL',
                'phone' => '+221 77 666 66 66',
            ],
            [
                'name' => 'Alassane Cissé',
                'email' => 'a.cisse@entreprise.sn',
                'service' => 'IT',
                'phone' => '+221 77 777 77 77',
            ],
            [
                'name' => 'Aminata Touré',
                'email' => 'a.toure@entreprise.sn',
                'service' => 'RH',
                'phone' => '+221 77 888 88 88',
            ]
        ];

        foreach ($employees as $emp) {
            User::firstOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['name'],
                    'email' => $emp['email'],
                    'password' => Hash::make('password'),
                    'role' => 'employe',
                    'service_id' => $services->where('code', $emp['service'])->first()->id,
                    'phone' => $emp['phone'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
