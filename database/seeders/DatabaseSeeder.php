<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@countryyoghurt.ng',
                'role' => 'admin',
                'phone' => null,
                'state' => null,
                'lga' => null,
                'shop_name' => null,
                'address' => null,
                'password' => 'admin123',
            ],
            [
                'name' => 'Staff Supervisor',
                'email' => 'staff@countryyoghurt.ng',
                'role' => 'staff',
                'phone' => '08031234567',
                'state' => 'Bauchi',
                'lga' => 'Bauchi',
                'shop_name' => null,
                'address' => null,
                'password' => 'staff123',
            ],
            [
                'name' => 'Customer Desk',
                'email' => 'customer@countryyoghurt.ng',
                'role' => 'customer',
                'phone' => '08039876543',
                'state' => 'Bauchi',
                'lga' => 'Bauchi',
                'shop_name' => 'Country Yoghurt Retail Desk',
                'address' => 'No. 4 Muda Lawal Market Road, Bauchi',
                'password' => 'customer123',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'role'     => $data['role'],
                    'phone'    => $data['phone'],
                    'state'    => $data['state'],
                    'lga'      => $data['lga'],
                    'shop_name' => $data['shop_name'],
                    'address'  => $data['address'],
                    'password' => bcrypt($data['password']),
                ]
            );
        }

        $this->call([
            ProductSeeder::class,
            UserSeeder::class,
        ]);
    }
}
