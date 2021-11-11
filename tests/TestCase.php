<?php

namespace Matteomeloni\CloudwatchLogs\Tests;

use Matteomeloni\CloudwatchLogs\Builder;
use Matteomeloni\CloudwatchLogs\Client\Mock\Client;
use Matteomeloni\CloudwatchLogs\CloudWatchLogs;
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

    /**
     * @return CloudWatchLogs|__anonymous@9509
     */
    protected function getFakeModel()
    {
        return new class extends CloudWatchLogs {
            protected string $logGroupName = 'Test';
            protected string $logStreamName = 'test';
        };
    }

    /**
     * @param $fakeModel
     * @return Builder
     */
    protected function getBuilder($fakeModel): Builder
    {
        return new class($fakeModel) extends Builder {
            public function __construct(CloudWatchLogs $fakeModel)
            {
                $this->model = $fakeModel;
                $this->client = new Client($this->model->getLogGroupName(), $this->model->getLogStreamName());
            }
        };
    }
}
