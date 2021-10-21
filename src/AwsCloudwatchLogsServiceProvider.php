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
    }
}
