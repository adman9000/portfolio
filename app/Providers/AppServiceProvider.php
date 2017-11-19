<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Observers\MyActivityObserver;
use App\User;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Fix for user migrate error
         Schema::defaultStringLength(191);

        //List all models that need to be recorded in activity log - would like to be able to do this in env
        //Domain::observe(MyActivityObserver::class);
        User::observe(MyActivityObserver::class);
        Permission::observe(MyActivityObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        require_once __DIR__ . '/../Http/Helpers/Navigation.php';
    }
}
