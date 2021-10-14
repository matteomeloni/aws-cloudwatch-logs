<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Matteomeloni\AwsCloudwatchLogs\Client\Analyzer;
use Matteomeloni\AwsCloudwatchLogs\Client\Client;
use Matteomeloni\AwsCloudwatchLogs\Client\QueryBuilder;

class Builder
{
    /**
     * @var AwsCloudwatchLogs
     */
    protected AwsCloudwatchLogs $model;

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var array
     */
    protected array $wheres = [];

    /**
     * @param AwsCloudwatchLogs $model
     */
    public function __construct(AwsCloudwatchLogs $model)
    {
        $this->model = $model;

        $this->client = new Client($this->model->getLogGroupName(), $this->model->getLogStreamName());
    }

    /**
     * Add a "where" clause to the query.
     *
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, string $boolean = 'and'): Builder
    {
        if (func_num_args() == 2) {
            [$value, $operator] = [$operator, '='];
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' =>$operator,
            'value' => $value,
            'boolean' => $boolean,
        ];

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param string $column
     * @param string|null $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere(string $column, string $operator = null, $value = null): Builder
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereIn(string $column, array $values, string $boolean = 'and'): Builder
    {
        return $this->where($column, 'in', $values, $boolean);
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function orWhereIn(string $column, array $values): Builder
    {
        return $this->where($column, 'in', $values, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'and'): Builder
    {
        return $this->where($column, 'not in', $values, $boolean);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function orWhereNotIn(string $column, array $values): Builder
    {
        return $this->where($column, 'not in', $values, 'or');
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereNull(string $column, string $boolean = 'and', bool $not = false): Builder
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $not
                ? 'not isempty'
                : 'isempty',
            'value' => null,
            'boolean' => $boolean,
        ];

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param string $column
     * @return $this
     */
    public function orWhereNull(string $column): Builder
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @return $this
     */
    public function whereNotNull(string $column, string $boolean = 'and'): Builder
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param string $column
     * @return $this
     */
    public function orWhereNotNull(string $column): Builder
    {
        return $this->whereNotNull($column, 'or');
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
        if(count($this->wheres) > 0) {
            $raw = (new QueryBuilder($this->model, $this->wheres))->raw();
            dd($raw, $this->wheres);
        }

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
