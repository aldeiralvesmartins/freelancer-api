<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $clients = User::where('type', 'client')->get();

        if ($clients->isEmpty()) {
            // Cria clientes se ainda não existirem
            \App\Models\User::factory(10)->client()->create();
            $clients = User::where('type', 'client')->get();
        }

        foreach (range(1, 30) as $i) {
            $project = Project::factory()->make();
            $project->client_id = $clients->random()->id;
            $project->save();

            $categories = Category::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $project->categories()->attach($categories);


        }
    }
}
