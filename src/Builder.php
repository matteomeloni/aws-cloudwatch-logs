<?php

namespace Matteomeloni\AwsCloudwatchLogs;

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
