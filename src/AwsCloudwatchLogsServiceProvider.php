<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\ServiceProvider;

class AwsCloudwatchLogsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'matteomeloni');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'matteomeloni');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aws-cloudwatch-logs.php', 'aws-cloudwatch-logs');

        // Register the service the package provides.
        $this->app->singleton('aws-cloudwatch-logs', function ($app) {
            return new AwsCloudwatchLogs;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['aws-cloudwatch-logs'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/aws-cloudwatch-logs.php' => config_path('aws-cloudwatch-logs.php'),
        ], 'aws-cloudwatch-logs.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/matteomeloni'),
        ], 'aws-cloudwatch-logs.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/matteomeloni'),
        ], 'aws-cloudwatch-logs.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/matteomeloni'),
        ], 'aws-cloudwatch-logs.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
