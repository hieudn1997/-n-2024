<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')->group(function () {
                Route::prefix('api')->group(base_path('routes/api.php'));
                Route::middleware('auth:sanctum')->group(function () {
                    Route::middleware('admin.role')->group(function () {
                        Route::prefix('api/admin')->group(base_path('routes/api.admin.php'));
                    });
                    Route::prefix('api/me')->group(base_path('routes/api.me.php'));
                    Route::prefix('api/packages')->group(base_path('routes/api.packages.php'));
                    Route::prefix('api/funds')->group(base_path('routes/api.funds.php'));
                    Route::prefix('api/transactions')->group(base_path('routes/api.transactions.php'));
                    Route::prefix('api/notifications')->group(base_path('routes/api.notifications.php'));
                });
            });

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
