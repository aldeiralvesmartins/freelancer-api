<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Proposal;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        Proposal::inRandomOrder()->take(20)->get()->each(function ($proposal) {
            Payment::create([
                'proposal_id' => $proposal->id,
                'amount' => $proposal->amount,
                'fee' => $proposal->amount * 0.1,
                'status' => fake()->randomElement(['pending', 'held', 'released', 'refunded'])
            ]);
        });
    }
}
