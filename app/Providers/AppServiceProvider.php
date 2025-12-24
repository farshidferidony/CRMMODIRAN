<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Blade;
use App\Services\AccessService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);

        Blade::if('canAccess', function (string $permissionName) {
            
            $user = auth()->user();
            if (! $user) {
                return false;
            }

            /** @var \App\Services\AccessService $access */
            $access = app(AccessService::class);

            return $access->userHasPermission($user, $permissionName);
            
        });
    }
}
