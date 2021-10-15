<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\Collection;

class AwsCloudWatchLogsCollection extends Collection
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
    public function getCloudWatchLogsInsightQueryStatus(): string
    {
        return $this->cloudWatchLogsInsightQueryStatus;
    }

    /**
     * @return string
     */
    public function getCloudWatchLogsInsightQueryId(): string
    {
        return $this->cloudWatchLogsInsightQueryId;
    }

    /**
     * @param string|null $cloudWatchLogsInsightQueryStatus
     * @return AwsCloudWatchLogsCollection
     */
    public function setCloudWatchLogsInsightQueryStatus(string $cloudWatchLogsInsightQueryStatus = null): AwsCloudWatchLogsCollection
    {
        $this->cloudWatchLogsInsightQueryStatus = $cloudWatchLogsInsightQueryStatus;

        return $this;
    }

    /**
     * @param string|null $cloudWatchLogsInsightQueryId
     * @return AwsCloudWatchLogsCollection
     */
    public function setCloudWatchLogsInsightQueryId(string $cloudWatchLogsInsightQueryId = null): AwsCloudWatchLogsCollection
    {
        $this->cloudWatchLogsInsightQueryId = $cloudWatchLogsInsightQueryId;

        return $this;
    }
}
