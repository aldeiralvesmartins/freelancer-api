<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class TransactionsTableSeeder extends Seeder
{
    public function run()
    {
        $wallets = Wallet::all();

        $types = ['deposit', 'withdrawal', 'lock', 'unlock', 'release'];

        foreach ($wallets as $wallet) {
            for ($i = 0; $i < 5; $i++) {
                \DB::table('transactions')->insert([
                    'wallet_id' => $wallet->id,
                    'type' => $types[array_rand($types)],
                    'amount' => mt_rand(1000, 10000) / 100,
                    'related_id' => \Illuminate\Support\Str::uuid(),
                    'related_type' => 'project',
                    'description' => 'Transação de teste #' . ($i + 1),
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
