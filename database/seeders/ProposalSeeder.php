<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proposal;
use App\Models\Project;
use App\Models\User;

class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        $freelancers = User::where('type', 'freelancer')->get();

        Project::all()->each(function ($project) use ($freelancers) {
            // Lista dos freelancers que já fizeram proposta para esse projeto
            $alreadyProposedFreelancers = [];

            // Quantidade aleatória de propostas para o projeto
            $proposalsCount = rand(1, 5);

            for ($i = 0; $i < $proposalsCount; $i++) {
                // Filtra os freelancers que ainda não propuseram para esse projeto
                $availableFreelancers = $freelancers->filter(function ($freelancer) use ($alreadyProposedFreelancers) {
                    return !in_array($freelancer->id, $alreadyProposedFreelancers);
                });

                // Se não tiver mais freelancers disponíveis, sai do loop
                if ($availableFreelancers->isEmpty()) {
                    break;
                }

                // Escolhe um freelancer aleatório dentre os disponíveis
                $freelancer = $availableFreelancers->random();

                // Cria a proposta
                Proposal::create([
                    'project_id' => $project->id,
                    'freelancer_id' => $freelancer->id,
                    'amount' => rand(500, 10000),
                    'duration' => rand(3, 30),
                    'message' => 'Proposta automática',
                    'links' => ['https://github.com/example'],
                    'status' => 'pending',
                ]);

                // Marca esse freelancer como já tendo proposto para este projeto
                $alreadyProposedFreelancers[] = $freelancer->id;
            }
        });
    }
}
