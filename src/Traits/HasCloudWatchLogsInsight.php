<?php

namespace Matteomeloni\AwsCloudwatchLogs\Traits;

use Matteomeloni\AwsCloudwatchLogs\Collections\LogsCollection;

trait HasCloudWatchLogsInsight
{
    /**
     * @var string|null
     */
    private ?string $cloudWatchLogsInsightQueryStatus;

    /**
     * @var string|null
     */
    private ?string $cloudWatchLogsInsightQueryId;

    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    /**
     * @return string
     */
    public function getQueryStatus(): string
    {
        return $this->cloudWatchLogsInsightQueryStatus;
    }

    /**
     * @return string
     */
    public function getQueryId(): string
    {
        return $this->cloudWatchLogsInsightQueryId;
    }

    /**
     * @param string|null $cloudWatchLogsInsightQueryStatus
     * @return LogsCollection
     */
    public function setQueryStatus(string $cloudWatchLogsInsightQueryStatus = null): LogsCollection
    {
        $this->cloudWatchLogsInsightQueryStatus = $cloudWatchLogsInsightQueryStatus;

        return $this;
    }

    /**
     * @param string|null $cloudWatchLogsInsightQueryId
     * @return LogsCollection
     */
    public function setQueryId(string $cloudWatchLogsInsightQueryId = null): LogsCollection
    {
        $this->cloudWatchLogsInsightQueryId = $cloudWatchLogsInsightQueryId;

        return $this;
    }
}
