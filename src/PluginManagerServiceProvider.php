<?php

namespace Baijunyao\LaravelPluginManager;

use Illuminate\Contracts\Http\Kernel;
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
            __DIR__.'/config/pluginManager.php' => config_path('pluginManager.php'),
        ]);
        
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware(PluginManager::class);
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
