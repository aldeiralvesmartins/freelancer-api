<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wallet;
use App\Models\User;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => rand(0, 5000)
            ]);
        });
    }
}
