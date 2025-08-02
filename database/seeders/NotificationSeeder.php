<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {
            foreach (range(1, rand(1, 5)) as $i) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'info',
                    'message' => "Notificação automática {$i}",
                    'read' => (bool)rand(0, 1)
                ]);
            }
        });
    }
}
