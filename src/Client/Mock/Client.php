<?php

namespace Matteomeloni\CloudwatchLogs\Client\Mock;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\MockHandler;
use Aws\Result;
use Illuminate\Support\Str;
use Matteomeloni\CloudwatchLogs\Client\ClientInterface;
use Matteomeloni\CloudwatchLogs\Traits\HasMessageParser;

class Client implements ClientInterface
{
    use HasMessageParser;

    /**
     * The name of the log group.
     *
     * @var string
     */
    private string $logGroupName;

    /**
     * The name of the log stream.
     *
     * @var string;
     */
    private string $logStreamName;

    /**
     * Create a new Client instance.
     *
     * @param $logGroupName
     * @param $logStreamName
     */
    public function __construct($logGroupName, $logStreamName)
    {
        $this->logGroupName = $logGroupName;
        $this->logStreamName = $logStreamName;
    }

    public function getLogEvents(array $timeRange, bool $startFromHead): array
    {
        $options = [
            'logGroupName' => $this->logGroupName,
            'logStreamName' => $this->logStreamName,
        ];

        $logs = $this->mockConnection(['events' => (new Factory(15))->logs()])
            ->getLogEvents($options)
            ->toArray();

        return $logs['events'] ?? [];
    }

    public function startQuery(array $timeRange, string $query): ?string
    {
        return Str::uuid();
    }

    public function getQueryResults(string $queryId): array
    {
        return $this->mockConnection([
            'queryId' => $queryId,
            'status' => 'Complete',
            'results' => (new Factory(15))->logs()
        ])->getQueryResults([
            'queryId' => $queryId
        ])->toArray();
    }

    public function describeQueries(): array
    {
        $queries = $this->mockConnection(['queries' => (new Factory(5))->queries()])
            ->describeQueries(['logGroupName' => $this->logGroupName,])
            ->toArray();

        return $queries['queries'];
    }

    public function getLogRecord(string $ptr): ?array
    {
        return $this->mockConnection(['events' => (new Factory())->logs()])
            ->getLogRecord(['logRecordPointer' => $ptr])
            ->toArray()['events'][1];
    }

    public function putLogEvents(array $data = []): bool
    {
        return true;
    }

    /**
     * @param array $data
     * @return CloudWatchLogsClient
     */
    private function mockConnection(array $data = []): CloudWatchLogsClient
    {
        $mock = new MockHandler();

        if (!empty($data)) {
            $mock->append(new Result($data));
        }

        return new CloudWatchLogsClient([
            'version' => 'latest',
            'region' => 'us-west-2',
            'handler' => $mock,
            'credentials' => [
                'key' => 'xxx',
                'secret' => 'xxx'
            ]
        ]);
    }
}
