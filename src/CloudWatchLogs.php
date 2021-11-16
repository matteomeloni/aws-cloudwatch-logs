<?php

namespace Matteomeloni\CloudwatchLogs;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonSerializable;
use Matteomeloni\CloudwatchLogs\Collections\LogsCollection;

abstract class CloudWatchLogs implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    use HasAttributes,
        ForwardsCalls;

    /**
     * The name of the log group.
     *
     * @var string
     */
    protected string $logGroupName;

    /**
     * The name of the log stream.
     *
     * @var string;
     */
    protected string $logStreamName;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

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
    public function setLogGroupName(string $logGroupName): CloudWatchLogs
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
    public function setLogStreamName(string $logStreamName): CloudWatchLogs
    {
        $this->logStreamName = $logStreamName;

        return $this;
    }

    /**
     * Save the log to Aws CloudWatch Logs.
     *
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
    public function fill(array $attributes): CloudWatchLogs
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
    public function newInstance(array $attributes = []): CloudWatchLogs
    {
        $model = new static($attributes);

        $model->setLogGroupName($this->getLogGroupName());
        $model->setLogStreamName($this->getLogStreamName());

        return $model;
    }

    /**
     * Create a new Query Builder for the model.
     *
     * @return Builder
     */
    public function builder(): Builder
    {
        return new Builder($this);
    }

    /**
     * Create a new AwsCloudWatchLogsCollection Instance.
     *
     * @param array $models
     * @return LogsCollection
     */
    public function newCollection(array $models = []): LogsCollection
    {
        return new LogsCollection($models);
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

    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
