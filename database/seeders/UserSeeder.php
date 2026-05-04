<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $states = array_keys(config('nigeria.lgas'));

        $firstNames = [
            'Amina', 'Bello', 'Chidi', 'Damilola', 'Emeka', 'Fatima', 'Gbenga', 'Hauwa',
            'Ibrahim', 'Juliet', 'Kabiru', 'Lola', 'Musa', 'Ngozi', 'Olu', 'Patience',
            'Quadri', 'Rashida', 'Samuel', 'Titilayo', 'Usman', 'Victoria', 'Wale', 'Yakubu',
            'Zainab', 'Adaeze', 'Babatunde', 'Chiamaka', 'Danjuma', 'Efua',
        ];

        $lastNames = [
            'Abubakar', 'Balogun', 'Chukwu', 'Danjuma', 'Eze', 'Fashola', 'Garba', 'Hassan',
            'Inuwa', 'Johnson', 'Kalu', 'Lawal', 'Musa', 'Nwosu', 'Okonkwo', 'Peters',
            'Raji', 'Sule', 'Tunde', 'Uche', 'Vincent', 'Waziri', 'Yusuf', 'Zubair',
            'Adeyemi', 'Bello', 'Chima', 'Dauda', 'Ezeudo', 'Fagbemi',
        ];

        $phonePrefixes = [
            '0801', '0802', '0803', '0806', '0807', '0808',
            '0809', '0701', '0703', '0706', '0901', '0902', '0903',
        ];

        $n = count($firstNames);
        $l = count($lastNames);
        $p = count($phonePrefixes);

        foreach ($states as $index => $state) {
            $lgas = config('nigeria.lgas.' . $state);
            $slug = strtolower(str_replace(' ', '.', $state));

            // 2 staff per state
            for ($i = 0; $i < 2; $i++) {
                $firstName = $firstNames[($index * 4 + $i) % $n];
                $lastName  = $lastNames[($index * 4 + $i + 7) % $l];
                $phone     = $phonePrefixes[($index + $i) % $p] . str_pad((string)(1000000 + $index * 10 + $i), 7, '0', STR_PAD_LEFT);

                User::updateOrCreate(
                    ['email' => "staff.{$slug}.{$i}@countryyoghurt.ng"],
                    [
                        'name'      => "{$firstName} {$lastName}",
                        'role'      => 'staff',
                        'phone'     => $phone,
                        'state'     => $state,
                        'lga'       => $lgas[0],
                        'shop_name' => null,
                        'address'   => null,
                        'password'  => bcrypt('staff123'),
                    ]
                );
            }

            // 2 customers per state
            for ($i = 0; $i < 2; $i++) {
                $firstName = $firstNames[($index * 4 + $i + 15) % $n];
                $lastName  = $lastNames[($index * 4 + $i + 20) % $l];
                $phone     = $phonePrefixes[($index + $i + 4) % $p] . str_pad((string)(2000000 + $index * 10 + $i), 7, '0', STR_PAD_LEFT);
                $lga       = $lgas[$i % count($lgas)];

                User::updateOrCreate(
                    ['email' => "customer.{$slug}.{$i}@countryyoghurt.ng"],
                    [
                        'name'      => "{$firstName} {$lastName}",
                        'role'      => 'customer',
                        'phone'     => $phone,
                        'state'     => $state,
                        'lga'       => $lga,
                        'shop_name' => "{$firstName}'s Yoghurt Shop",
                        'address'   => 'No. ' . (($index + $i + 1) * 3) . ' Market Road, ' . $lga,
                        'password'  => bcrypt('customer123'),
                    ]
                );
            }
        }
    }
}
