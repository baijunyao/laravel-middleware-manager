<?php

namespace Baijunyao\LaravelPluginManager;

use Illuminate\Support\ServiceProvider;
use Baijunyao\LaravelPluginManager\Middleware\PluginManager;

class PluginManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置项
        $this->publishes([
            __DIR__.'/config/laravel-plugin-manager.php' => config_path('laravel-plugin-manager.php'),
        ]);

        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', PluginManager::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
