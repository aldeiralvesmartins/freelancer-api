<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * O namespace base para os controladores da aplicação.
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Caminho para a "home" da aplicação.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define os grupos de rotas da aplicação.
     */
    public function boot(): void
    {
        parent::boot();

        $this->routes(function () {
            // Rotas da API
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rotas da Web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
