<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Document;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::firstOrCreate(
            ['email' => 'manager@inmogest.test'],
            [
                'name' => 'Manager Demo',
                'password' => Hash::make('password123'),
                'role' => 'manager',
            ],
        );

        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@inmogest.test'],
            [
                'name' => 'Propietario Demo',
                'password' => Hash::make('password123'),
                'role' => 'owner',
            ],
        );

        $demoOwner = Owner::firstOrCreate(
            ['email' => $ownerUser->email],
            [
                'user_id' => $ownerUser->id,
                'name' => $ownerUser->name,
                'phone' => '+34600000000',
                'dni' => '12345678A',
                'notes' => 'Usuario demo para la defensa del proyecto.',
            ],
        );

        $owners = collect([
            $demoOwner,
            Owner::updateOrCreate(
                ['email' => 'ana.garcia@clientes.test'],
                [
                    'name' => 'Ana García',
                    'phone' => '+34622222222',
                    'dni' => '11111111A',
                    'notes' => 'Cliente con varias viviendas en gestión.',
                ],
            ),
            Owner::updateOrCreate(
                ['email' => 'carlos.ruiz@clientes.test'],
                [
                    'name' => 'Carlos Ruiz',
                    'phone' => '+34633333333',
                    'dni' => '22222222B',
                    'notes' => 'Propietario interesado en alquiler residencial.',
                ],
            ),
        ]);

        $properties = collect([
            Property::updateOrCreate(
                ['title' => 'Piso luminoso en Valencia centro'],
                [
                    'address' => 'Calle Colón 24, Valencia',
                    'price' => 950,
                    'size' => 86,
                    'rooms' => 3,
                    'bathrooms' => 2,
                    'status' => 'rented',
                    'description' => 'Vivienda preparada para alquiler de larga estancia.',
                ],
            ),
            Property::updateOrCreate(
                ['title' => 'Ático con terraza en Alicante'],
                [
                    'address' => 'Avenida Maisonnave 10, Alicante',
                    'price' => 1250,
                    'size' => 104,
                    'rooms' => 2,
                    'bathrooms' => 2,
                    'status' => 'available',
                    'description' => 'Ático con terraza y plaza de garaje.',
                ],
            ),
            Property::updateOrCreate(
                ['title' => 'Apartamento junto al campus'],
                [
                    'address' => 'Calle Universitat 8, Castellón',
                    'price' => 720,
                    'size' => 64,
                    'rooms' => 2,
                    'bathrooms' => 1,
                    'status' => 'available',
                    'description' => 'Apartamento orientado a estudiantes o jóvenes profesionales.',
                ],
            ),
            Property::updateOrCreate(
                ['title' => 'Casa familiar en Elche'],
                [
                    'address' => 'Calle Palmeral 15, Elche',
                    'price' => 1100,
                    'size' => 132,
                    'rooms' => 4,
                    'bathrooms' => 2,
                    'status' => 'maintenance',
                    'description' => 'Casa en revisión antes de volver al mercado.',
                ],
            ),
        ]);

        $properties[0]->owners()->syncWithoutDetaching([$demoOwner->id]);
        $properties[1]->owners()->syncWithoutDetaching([$demoOwner->id]);
        $properties[2]->owners()->syncWithoutDetaching([$owners[1]->id]);
        $properties[3]->owners()->syncWithoutDetaching([$owners[2]->id]);

        $tenants = collect([
            Tenant::updateOrCreate(
                ['email' => 'laura.martinez@example.test'],
                [
                    'name' => 'Laura Martínez',
                    'phone' => '+34611111111',
                    'dni' => '87654321B',
                    'owner_id' => $demoOwner->id,
                    'notes' => 'Inquilina demo asociada al propietario principal.',
                ],
            ),
            Tenant::updateOrCreate(
                ['email' => 'pablo.soler@example.test'],
                [
                    'name' => 'Pablo Soler',
                    'phone' => '+34644444444',
                    'dni' => '33333333C',
                    'owner_id' => $owners[1]->id,
                    'notes' => 'Contrato pendiente de renovación.',
                ],
            ),
            Tenant::updateOrCreate(
                ['email' => 'marta.lopez@example.test'],
                [
                    'name' => 'Marta López',
                    'phone' => '+34655555555',
                    'dni' => '44444444D',
                    'owner_id' => $owners[2]->id,
                    'notes' => 'Pago por transferencia.',
                ],
            ),
        ]);

        $tenants[0]->properties()->syncWithoutDetaching([$properties[0]->id]);
        $tenants[1]->properties()->syncWithoutDetaching([$properties[2]->id]);
        $tenants[2]->properties()->syncWithoutDetaching([$properties[3]->id]);

        $contracts = collect([
            Contract::updateOrCreate(
                ['property_id' => $properties[0]->id, 'start_date' => now()->subMonths(2)->format('Y-m-d')],
                [
                    'end_date' => now()->addMonths(10)->format('Y-m-d'),
                    'rent_price' => 950,
                    'deposit' => 950,
                    'status' => 'active',
                ],
            ),
            Contract::updateOrCreate(
                ['property_id' => $properties[2]->id, 'start_date' => now()->subMonths(6)->format('Y-m-d')],
                [
                    'end_date' => now()->addMonths(6)->format('Y-m-d'),
                    'rent_price' => 720,
                    'deposit' => 720,
                    'status' => 'active',
                ],
            ),
            Contract::updateOrCreate(
                ['property_id' => $properties[3]->id, 'start_date' => now()->subYear()->format('Y-m-d')],
                [
                    'end_date' => now()->subMonth()->format('Y-m-d'),
                    'rent_price' => 1100,
                    'deposit' => 1100,
                    'status' => 'finished',
                ],
            ),
        ]);

        $contracts[0]->tenants()->syncWithoutDetaching([$tenants[0]->id]);
        $contracts[1]->tenants()->syncWithoutDetaching([$tenants[1]->id]);
        $contracts[2]->tenants()->syncWithoutDetaching([$tenants[2]->id]);

        foreach ($contracts as $index => $contract) {
            Payment::updateOrCreate(
                [
                    'contract_id' => $contract->id,
                    'payment_date' => now()->subMonths($index)->format('Y-m-d'),
                ],
                [
                    'amount' => $contract->rent_price,
                    'method' => $index === 0 ? 'transfer' : 'cash',
                    'notes' => 'Pago demo generado por seeder.',
                ],
            );
        }

        Storage::disk('public')->put(
            'documents/demo-contrato.txt',
            'Documento de demostración para InmoGest.',
        );

        Document::updateOrCreate(
            ['file_path' => 'documents/demo-contrato.txt'],
            [
                'name' => 'Contrato demo',
                'type' => 'contract',
                'property_id' => $properties[0]->id,
                'tenant_id' => null,
                'contract_id' => $contracts[0]->id,
            ],
        );
    }
}
