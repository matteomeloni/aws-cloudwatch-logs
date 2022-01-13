<?php

namespace Matteomeloni\CloudwatchLogs\Client;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Illuminate\Support\Str;
use Matteomeloni\CloudwatchLogs\Contracts\ClientInterface;
use Matteomeloni\CloudwatchLogs\Traits\HasMessageParser;

class Client implements ClientInterface
{
    use HasMessageParser;

    /**
     * @var CloudWatchLogsClient
     */
    private CloudWatchLogsClient $client;

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

        $this->client = $this->connect();
    }

    /**
     * Lists log events from the specified log stream.
     * You can list all the log events or filter using a time range.
     *
     * @param array $timeRange
     * @param bool $startFromHead
     * @return array
     */
    public function getLogEvents(array $timeRange, bool $startFromHead): array
    {
        [$startTime, $endTime] = $timeRange;

        $logs = $this->client->getLogEvents([
            'logGroupName' => $this->logGroupName,
            'logStreamName' => $this->logStreamName,
            'startFromHead' => $startFromHead,
            'startTime' => $startTime,
            'endTime' => $endTime
        ])->toArray();

        return $logs['events'] ?? [];
    }

    /**
     * Schedules a query of a log group using CloudWatch Logs Insights.
     * You specify the log group and time range to query and the query string to use.
     *
     * @param array $timeRange
     * @param string $query
     * @return string|null
     */
    public function startQuery(array $timeRange, string $query): ?string
    {
        [$startTime, $endTime] = $timeRange;

        $logs = $this->client->startQuery([
            'logGroupName' => $this->logGroupName,
            'logStreamName' => $this->logStreamName,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'queryString' => $query
        ])->toArray();

        return $logs['queryId'] ?? null;
    }

    /**
     * Returns the results from the specified query.
     * If the value of the Status field in the output is Running, this operation returns only partial results.
     * If you see a value of Scheduled or Running for the status, you can retry the operation later to see the final results.
     *
     * @param string $queryId
     * @return array
     */
    public function getQueryResults(string $queryId): array
    {
        $logs = $this->client->getQueryResults([
            'queryId' => $queryId
        ])->toArray();

        $result = collect($logs['results'])
            ->map(function ($log) {
                return (collect($log))->mapWithKeys(function ($item) {
                    $field = Str::replace('@', '', $item['field']);
                    $value = $item['value'];
                    return [$field => $value];
                });
            })->toArray();

        return [
            'queryId' => $queryId,
            'status' => $logs['status'],
            'results' => $result
        ];
    }

    /**
     * Returns a list of CloudWatch Logs Insights queries that are scheduled, executing, or have been executed recently in this account.
     *
     * @return array
     */
    public function describeQueries(): array
    {
        $queries = $this->client->describeQueries([
            'logGroupName' => $this->logGroupName,
        ])->toArray();

        return $queries['queries'];
    }

    /**
     * Retrieves all the fields and values of a single log event
     *
     * @param string $ptr
     * @return array|null
     */
    public function getLogRecord(string $ptr): ?array
    {
        try {
            $result = $this->client->getLogRecord([
                'logRecordPointer' => $ptr
            ])->toArray();

            return [
                'ingestionTime' => (int)$result['logRecord']['@ingestionTime'] ?? null,
                'timestamp' => (int)$result['logRecord']['@timestamp'] ?? null,
                'message' => $result['logRecord']['@message'] ?? null,
                'ptr' => $ptr
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Uploads a batch of log events to the specified log stream.
     *
     * @param array $data
     * @return bool
     */
    public function putLogEvents(array $data = []): bool
    {
        $nextSequenceToken = new SequenceToken($this->client, $this->logGroupName, $this->logStreamName);

        $options = [
            'logGroupName' => $this->logGroupName,
            'logStreamName' => $this->logStreamName,
            'logEvents' => [[
                'message' => $this->getRawMessage($data),
                'timestamp' => now()->getTimestampMs()
            ]]
        ];

        if ($nextSequenceToken->nextSequenceTokenIsRequired()) {
            $options['sequenceToken'] = $nextSequenceToken->retrieveNextSequenceToken();
        }

        $result = $this->client
            ->putLogEvents($options)
            ->toArray();

        return $result['@metadata']['statusCode'] === 200;
    }

    /**
     * @return CloudWatchLogsClient
     */
    private function connect(): CloudWatchLogsClient
    {
        return new CloudWatchLogsClient([
            'version' => 'latest',
            'region' => config('aws-cloudwatch-logs.region'),
            'credentials' => [
                'key' => config('aws-cloudwatch-logs.key'),
                'secret' => config('aws-cloudwatch-logs.secret')
            ]
        ]);
    }
}
