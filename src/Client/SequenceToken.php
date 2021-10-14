<?php

namespace Matteomeloni\AwsCloudwatchLogs\Client;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

class SequenceToken
{
    /**
     * @var CloudWatchLogsClient
     */
    private $client;

    /**
     * @var array
     */
    private $info;

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
     * @param CloudWatchLogsClient $client
     * @param $logGroupName
     * @param $logStreamName
     */
    public function __construct(CloudWatchLogsClient $client, $logGroupName, $logStreamName)
    {
        $this->client = $client;
        $this->logGroupName = $logGroupName;
        $this->logStreamName = $logStreamName;

        $this->info = $this->describeLogEvents();
    }

    /**
     * @return bool
     */
    public function nextSequenceTokenIsRequired(): bool
    {
        return isset($this->info['uploadSequenceToken']);
    }

    /**
     * @return string|null
     */
    public function retrieveNextSequenceToken(): ?string
    {
        return $this->info['uploadSequenceToken'] ?? null;
    }

    /**
     * @return array
     */
    private function describeLogEvents(): array
    {
        $info = $this->client
            ->describeLogStreams([
                'logGroupName' => $this->logGroupName,
                'logStreamNamePrefix' => $this->logStreamName
            ])
            ->toArray();

        return $info['logStreams'][0] ?? [];
    }
}
