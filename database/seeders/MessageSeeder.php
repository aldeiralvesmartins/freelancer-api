<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;
use App\Models\Project;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $projects = Project::all();

        for ($i = 0; $i < 100; $i++) {
            $sender = $users->random();
            $receiver = $users->where('id', '!=', $sender->id)->random();

            Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'project_id' => $projects->random()->id,
                'content' => 'Mensagem automática #' . $i
            ]);
        }
    }
}
