<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\Carbon;
use Matteomeloni\AwsCloudwatchLogs\Client\Analyzer;
use Matteomeloni\AwsCloudwatchLogs\Client\Client;

class Builder
{
    /**
     * @var AwsCloudwatchLogs
     */
    protected $model;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param AwsCloudwatchLogs $model
     */
    public function __construct(AwsCloudwatchLogs $model)
    {
        $this->model = $model;

        $this->client = new Client($this->model->getLogGroupName(), $this->model->getLogStreamName());
    }

    /**
     * Get all logs.
     *
     * @param int|null $startTime
     * @param int|null $endTime
     * @param bool $startFromHead
     * @return AwsCloudWatchLogsCollection
     */
    public function get(int $startTime = null, int $endTime = null, bool $startFromHead = true): AwsCloudWatchLogsCollection
    {
        $startTime ??= now()->startOfDay()->timestamp * 1000;
        $endTime ??= now()->endOfDay()->timestamp * 1000;

        return $this->all($startTime, $endTime, $startFromHead);
    }

    /**
     * Get all logs.
     *
     * @param int|null $startTime
     * @param int|null $endTime
     * @param bool $startFromHead
     * @return AwsCloudWatchLogsCollection
     */
    public function all(int $startTime = null, int $endTime = null, bool $startFromHead = true): AwsCloudWatchLogsCollection
    {
        $startTime ??= now()->startOfDay()->timestamp * 1000;
        $endTime ??= now()->endOfDay()->timestamp * 1000;

        return $this->getAll($startTime, $endTime, $startFromHead);
    }

    /**
     * @param int $startTime
     * @param int $endTime
     * @param bool $startFromHead
     * @return AwsCloudWatchLogsCollection
     */
    private function getAll(int $startTime, int $endTime, bool $startFromHead): AwsCloudWatchLogsCollection
    {
        $iterator = $this->client
            ->getLogEvents($startTime,$endTime,$startFromHead);

        $results = [];
        foreach ($iterator as $item) {
            $model = $this->newModelInstance(
                (new Analyzer($item))->beautifyLog()
            );

            $results[] = $model;
        }

        return $this->model->newCollection($results);
    }

    public function create($attributes = [])
    {
        $newModelInstance = $this->newModelInstance($attributes);

        return tap($newModelInstance, function (AwsCloudwatchLogs $instance) {
            $instance->save();
        });
    }

    /**
     * Insert new log into the aws cloudwatch logs stream.
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes = []): bool
    {
        return $this->client->putLogEvents($attributes);
    }

    /**
     * Create a new instance of the model being queried.
     *
     * @param array $attributes
     * @return AwsCloudwatchLogs
     */
    private function newModelInstance(array $attributes = []): AwsCloudwatchLogs
    {
        return $this->model->newInstance($attributes);
    }

}
