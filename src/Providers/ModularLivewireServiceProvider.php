<?php

declare(strict_types=1);

namespace ArtisanBuild\ModularLivewire\Providers;

use ArtisanBuild\ModularLivewire\Commands\MakeLivewireCommand;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand as LivewireMakeCommand;

class ModularLivewireServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register our command override via the "booted" event to ensure we override
        // the default Livewire behavior regardless of which service provider happens
        // to be bootstrapped first
        $this->app->booted(function () {
            Artisan::starting(function (Artisan $artisan) {
                $this->registerLivewireOverride($artisan);
            });
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register the Livewire make:livewire command override.
     */
    protected function registerLivewireOverride(Artisan $artisan): void
    {
        // Don't register if Livewire isn't installed
        if (! class_exists(LivewireMakeCommand::class)) {
            return;
        }

        // Replace the resolved command with our subclass
        $artisan->resolveCommands([MakeLivewireCommand::class]);

        // Ensure that if 'make:livewire' is resolved from the container in the future,
        // our subclass is used instead
        $this->app->extend(LivewireMakeCommand::class, function () {
            return new MakeLivewireCommand;
        });
    }
}
