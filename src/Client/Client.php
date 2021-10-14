<?php

namespace Matteomeloni\AwsCloudwatchLogs\Client;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

class Client
{
    /**
     * @var CloudWatchLogsClient
     */
    private $client;

    /**
     * The sequence token obtained from the response of the previous PutLogEvents call.
     *
     * @var SequenceToken
     */
    private $nextSequenceToken;

    /**
     * The name of the log group.
     *
     * @var string
     */
    private $logGroupName;

    /**
     * The name of the log stream.
     *
     * @var string;
     */
    private $logStreamName;

    /**
     * @param $logGroupName
     * @param $logStreamName
     */
    public function __construct($logGroupName, $logStreamName)
    {
        $this->logGroupName = $logGroupName;
        $this->logStreamName = $logStreamName;

        $this->client = $this->connect();

        $this->nextSequenceToken = new SequenceToken($this->client, $this->logGroupName, $this->logStreamName);
    }

    /**
     * Lists log events from the specified log stream.
     * You can list all the log events or filter using a time range.
     *
     * @param int $startTime
     * @param int $endTime
     * @param bool $startFromHead
     * @return array
     */
    public function getLogEvents(int $startTime, int $endTime, bool $startFromHead): array
    {
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
     * Uploads a batch of log events to the specified log stream.
     *
     * @param array $data
     * @return bool
     */
    public function putLogEvents(array $data = []): bool
    {
        $options = [
            'logGroupName' => $this->logGroupName,
            'logStreamName' => $this->logStreamName,
            'logEvents' => [[
                'message' => $this->prepareData($data),
                'timestamp' => $this->getTimestamp()
            ]]
        ];

        if ($this->nextSequenceToken->nextSequenceTokenIsRequired()) {
            $options['sequenceToken'] = $this->nextSequenceToken->retrieveNextSequenceToken();
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

    /**
     * @param $data
     * @return string
     */
    private function prepareData($data): string
    {
        if(is_array($data)) {
            $data = json_encode($data);
        }

        return $data;
    }

    /**
     * @return float
     */
    private function getTimestamp(): float
    {
        return round(microtime(true) * 1000);
    }
}
