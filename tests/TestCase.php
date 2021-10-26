<?php

namespace Matteomeloni\CloudwatchLogs\Tests;

use Matteomeloni\CloudwatchLogs\CloudwatchLogsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CloudwatchLogsServiceProvider::class];
    }

    public function createRawLog(int $dateTime): array
    {
        $message = [
            'level' => 'DEBUG',
            'level_code' => '100',
            'environment' => 'test',
            'description' => 'new log!'
        ];

        return [
            'timestamp' => $dateTime,
            'ingestionTime' => $dateTime,
            'message' => json_encode($message)
        ];

    }
}
