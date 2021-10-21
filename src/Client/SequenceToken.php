<?php

namespace Matteomeloni\AwsCloudwatchLogs\Client;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

class SequenceToken
{
    /**
     * @var CloudWatchLogsClient
     */
    private CloudWatchLogsClient $client;

    /**
     * @var array
     */
    private array $info;

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
     * Create a new Sequence Token Instance.
     *
     * @param CloudWatchLogsClient $client
     * @param string $logGroupName
     * @param string $logStreamName
     */
    public function __construct(CloudWatchLogsClient $client, string $logGroupName, string $logStreamName)
    {
        $this->client = $client;
        $this->logGroupName = $logGroupName;
        $this->logStreamName = $logStreamName;
        $this->info = $this->describeLogEvents();
    }

    /**
     * Check if next sequence token is required.
     *
     * @return bool
     */
    public function nextSequenceTokenIsRequired(): bool
    {
        return isset($this->info['uploadSequenceToken']);
    }

    /**
     * Return the next sequence token.
     *
     * @return string|null
     */
    public function retrieveNextSequenceToken(): ?string
    {
        return $this->info['uploadSequenceToken'] ?? null;
    }

    /**
     * Lists the log streams for the specified log group.
     * You can list all the log streams or filter the results by prefix.
     * You can also control how the results are ordered.
     *
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
