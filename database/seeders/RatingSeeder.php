<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rating;
use App\Models\User;
use App\Models\Project;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();

        foreach ($projects as $project) {
            $from = $users->random();
            $to = $users->where('id', '!=', $from->id)->random();

            Rating::create([
                'from_user_id' => $from->id,
                'to_user_id' => $to->id,
                'project_id' => $project->id,
                'rating' => rand(1, 5),
                'comment' => 'Avaliação gerada automaticamente'
            ]);
        }
    }
}
