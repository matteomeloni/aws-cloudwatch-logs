<?php

namespace Matteomeloni\AwsCloudwatchLogs\Facades;

use Illuminate\Support\Facades\Facade;

class AwsCloudwatchLogs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'aws-cloudwatch-logs';
    }
}
