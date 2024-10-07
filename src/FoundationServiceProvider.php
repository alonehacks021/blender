<?php

namespace Nhd\Foundation;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;

class FoundationServiceProvider extends ServiceProvider
{
    // Define policies and Livewire components for the package
    private $livewire = [
        'foundation-component' => \Nhd\Foundation\Http\Livewire\FoundationComponent::class,
    ];

    public function register()
    {
        $this->app->bind('foundation', function () {
            return new Foundation;
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'foundation');
    }

    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/Views', 'foundation');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'foundation');

        $this->registerLivewire();
        $this->handleViews();

        // Publish assets and config for the package if running in console
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('foundation.php'),
            ], 'foundation-config');

            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/foundation'),
            ], 'foundation-assets');

            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/foundation'),
            ], 'foundation-lang');
        }
    }

    private function registerLivewire()
    {
        foreach ($this->livewire as $name => $class) {
            Livewire::component($name, $class);
        }
    }

    private function handleViews()
    {
        Blade::componentNamespace('Nhd\\Foundation\\View\\Components', 'foundation');
        
        // Uncomment if using view composers
        // View::composer('*', function ($view) {
        //     if (config('foundation.ui') === 'bs5' && request()->is('foundation*')) {
        //         $view->setPath(str_replace('.blade.php', '.bs5.blade.php', $view->getPath()));
        //     }
        // });
    }
}
