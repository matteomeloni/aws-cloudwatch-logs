<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\Traits\ForwardsCalls;

abstract class AwsCloudwatchLogs
{
    use ForwardsCalls;

    /**
     * The name of the log group.
     *
     * @var string
     */
    protected $logGroupName;

    /**
     * The name of the log stream.
     *
     * @var string;
     */
    protected $logStreamName;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * Create a new Aws CloudWatch Logs model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get the name of the log group.
     *
     * @return string
     */
    public function getLogGroupName(): string
    {
        return $this->logGroupName;
    }

    /**
     * Get the name of the log stream.
     *
     * @return string
     */
    public function getLogStreamName(): string
    {
        return $this->logStreamName;
    }

    /**
     * Set the name of the log group.
     *
     * @param string $logGroupName
     * @return $this
     */
    public function setLogGroupName(string $logGroupName): AwsCloudwatchLogs
    {
        $this->logGroupName = $logGroupName;

        return $this;
    }

    /**
     * Set the name of the log group.
     *
     * @param string $logStreamName
     * @return $this
     */
    public function setLogStreamName(string $logStreamName): AwsCloudwatchLogs
    {
        $this->logStreamName = $logStreamName;

        return $this;
    }

    /**
     * Save the log to Aws CloudWatch Logs.
     * @return bool
     */
    public function save(): bool
    {
        $builder = $this->builder();

        return $builder->insert($this->attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return $this
     *
     */
    public function fill(array $attributes): AwsCloudwatchLogs
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = []): AwsCloudwatchLogs
    {
        $model = new static($attributes);

        $model->setLogGroupName($this->getLogGroupName());
        $model->setLogStreamName($this->getLogStreamName());

        return $model;
    }

    /**
     * Create a new Query Builder for the model.
     * @return Builder
     */
    public function builder(): Builder
    {
        return new Builder($this);
    }

    /**
     * @param array $models
     * @return AwsCloudWatchLogsCollection
     */
    public function newCollection(array $models = []): AwsCloudWatchLogsCollection
    {
        return new AwsCloudWatchLogsCollection($models);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->forwardCallTo($this->builder(), $method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->attributes[$key];
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value)
    {
       $this->attributes[$key] = $value;
    }
}
