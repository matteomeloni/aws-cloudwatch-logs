<?php

namespace Matteomeloni\AwsCloudwatchLogs\Tests;

use Matteomeloni\AwsCloudwatchLogs\AwsCloudwatchLogsServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [AwsCloudwatchLogsServiceProvider::class];
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
