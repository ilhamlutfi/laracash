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
        // User::factory(10)->create();

        User::create(
            [
                'name' => 'Ilham Lutfi',
                'email' => 'ilhamlutfi153@gmail.com',
                'password' => bcrypt('risa2312'),
            ],
            [
                'name' => 'Risa Rahmayani',
                'email' => 'risarahmayani@gmail.com',
                'password' => bcrypt('ilham0705'),
            ]
        );

        $this->call([
            CategorySeeder::class,
        ]);
    }
}
