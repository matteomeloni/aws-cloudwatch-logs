<?php

namespace Matteomeloni\CloudwatchLogs;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonSerializable;
use Matteomeloni\CloudwatchLogs\Collections\LogsCollection;

abstract class CloudWatchLogs implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    use HasAttributes,
        HidesAttributes,
        GuardsAttributes,
        HasTimestamps,
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
     * The primary key for the model.
     *
     * @var string|null
     */
    protected ?string $primaryKey = null;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected string $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public bool $incrementing = false;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

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
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

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

        return $this->attributesToArray();
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
        return $this->getAttribute($key);
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
        $this->setAttribute($key, $value);
    }

    public function offsetExists($offset): bool
    {
        return !is_null($this->getAttribute($offset));
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

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }

    /**
     * Set whether IDs are incrementing.
     *
     * @param bool $value
     * @return $this
     */
    public function setIncrementing(bool $value): CloudWatchLogs
    {
        $this->incrementing = $value;

        return $this;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }
}
