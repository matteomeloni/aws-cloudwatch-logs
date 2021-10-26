<?php

namespace Matteomeloni\CloudwatchLogs\Facades;

use Illuminate\Support\Facades\Facade;

class CloudwatchLogs extends Facade
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
