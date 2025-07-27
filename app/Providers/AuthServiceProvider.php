<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Project;
use App\Policies\ProjectPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        // Aqui você pode adicionar outras policies, ex:
        // Proposal::class => ProposalPolicy::class,
        // Message::class => MessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Você pode definir Gates aqui, se quiser, ex:
        // Gate::define('update-project', function ($user, $project) {
        //     return $user->id === $project->client_id;
        // });
    }
}
