<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(50)->create(); // 50 usuários

        $this->call([
            CategorySeeder::class,
            ProjectSeeder::class,
            ProposalSeeder::class,
            PaymentSeeder::class,
            MessageSeeder::class,
            NotificationSeeder::class,
            RatingSeeder::class,
            WalletSeeder::class,
        ]);
    }
}
