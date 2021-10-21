<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Matteomeloni\AwsCloudwatchLogs\Client\Analyzer;
use Matteomeloni\AwsCloudwatchLogs\Client\Client;
use Matteomeloni\AwsCloudwatchLogs\Client\QueryBuilder;
use Matteomeloni\AwsCloudwatchLogs\Exceptions\LogNotFoundException;

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
    protected array $columns = [];

    /**
     * @var array
     */
    protected array $wheres = [];

    /**
     * @var array
     */
    protected array $sorts = [];

    /**
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * @var bool
     */
    protected bool $startFromHead = true;

    /**
     * @var bool
     */
    private bool $useCloudWatchLogsInsight = false;

    /**
     * @var string|null
     */
    private ?string $cloudWatchLogsInsightQueryId;

    /**
     * @var string|null
     */
    private ?string $cloudWatchLogsInsightQueryStatus;

    /**
     * Create new Aws CloudWatch Logs instance.
     *
     * @param AwsCloudwatchLogs $model
     */
    public function __construct(AwsCloudwatchLogs $model)
    {
        $this->model = $model;

        $this->client = new Client($this->model->getLogGroupName(), $this->model->getLogStreamName());
    }

    /**
     * Set the columns to be selected.
     *
     * @param array $columns
     * @return $this
     */
    public function select(array $columns = []): Builder
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Schedules a query of a log group using CloudWatch Logs Insights.
     * If $queryId is not null, then retrieve a scheduled query.
     *
     * @param null $queryId
     * @return Builder
     */
    public function query($queryId = null): Builder
    {
        $this->useCloudWatchLogsInsight = true;
        $this->cloudWatchLogsInsightQueryId = $queryId;

        return $this;
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
            'operator' => $operator,
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
     * Add a "where between" clause to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereBetween(string $column, array $values, string $boolean = 'and'): Builder
    {
        return $this->where($column, 'between', $values, $boolean);
    }

    /**
     * Add an "or where between" clause to the query.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function orWhereBetween(string $column, array $values): Builder
    {
        return $this->where($column, 'between', $values, 'or');
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
     * Add an "order by" clause to the query.
     *
     * @param string $column
     * @param string $direction
     * @return Builder
     */
    public function orderBy(string $column, string $direction = 'asc'): Builder
    {
        $this->sorts[] = [
            'column' => $column,
            'direction' => $direction
        ];

        return $this;
    }

    /**
     * Add a descending "order by" clause to the query.
     *
     * @param string $column
     * @return $this
     */
    public function orderByDesc(string $column): Builder
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     * @return $this
     */
    public function limit(int $value): Builder
    {
        $this->limit = $value;

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param $value
     * @return $this|Builder
     */
    public function take($value): Builder
    {
        return $this->limit($value);
    }

    /**
     * Get all the models from the AWS CloudWatch Logs.
     *
     * @param array $columns
     * @return AwsCloudWatchLogsCollection
     */
    public function get(array $columns = ['*']): AwsCloudWatchLogsCollection
    {
        return $this->all($columns);
    }

    /**
     * Get all the models from the AWS CloudWatch Logs.
     *
     * @param array $columns
     * @return AwsCloudWatchLogsCollection
     */
    public function all(array $columns = ['*']): AwsCloudWatchLogsCollection
    {
        $this->columns = $columns;

        return $this->getAll();
    }

    /**
     * Get All Logs and make AwsCloudWatchLogsCollection.
     *
     * @return AwsCloudWatchLogsCollection
     */
    private function getAll(): AwsCloudWatchLogsCollection
    {
        $iterator = $this->retrieveLogs();

        $results = [];
        foreach ($iterator as $index => $item) {
            $results[$index] = $this->newModelInstance(
                (new Analyzer($item))->beautifyLog($this->columns)
            );
        }

        $collection = $this->model->newCollection($results);

        if ($this->useCloudWatchLogsInsight) {
            $collection->setCloudWatchLogsInsightQueryId($this->cloudWatchLogsInsightQueryId);
            $collection->setCloudWatchLogsInsightQueryStatus($this->cloudWatchLogsInsightQueryStatus);
        }

        return $collection;
    }

    /**
     * Retrieve logs from Aws CloudWatch.
     *
     * @return array
     */
    private function retrieveLogs(): array
    {
        $timeRange = $this->extractTimeRange();

        if (!$this->useCloudWatchLogsInsight) {
            return $this->client->getLogEvents($timeRange, $this->startFromHead);
        }

        $queryId = $this->cloudWatchLogsInsightQueryId
            ?: $this->client->startQuery($timeRange, $this->buildQuery());

        $result = $this->client->getQueryResults($queryId);

        $this->cloudWatchLogsInsightQueryId = $result['queryId'];
        $this->cloudWatchLogsInsightQueryStatus = $result['status'];

        return $result['results'];
    }

    /**
     * Find a model by its ptr.
     *
     * @param string $ptr
     * @param array $columns
     * @return AwsCloudwatchLogs|null
     */
    public function find(string $ptr, array $columns = ['*']): ?AwsCloudwatchLogs
    {
        $result = $this->client->getLogRecord($ptr);

        if ($result === null) {
            return null;
        }

        return $this->newModelInstance(
            (new Analyzer($result))->beautifyLog($columns)
        );
    }

    /**
     * Find multiple models by their ptr.
     *
     * @param array $ids
     * @param array $columns
     * @return AwsCloudWatchLogsCollection
     */
    public function findMany(array $ids, array $columns = ['*']): AwsCloudWatchLogsCollection
    {
        $results = [];

        foreach ($ids as $id) {
            $results[] = $this->find($id, $columns);
        }

        return $this->model->newCollection($results);
    }

    /**
     * Find a model by its ptr or throw an exception.
     *
     * @param string $ptr
     * @param array $columns
     * @return AwsCloudwatchLogs
     */
    public function findOrFail(string $ptr, array $columns = ['*']): AwsCloudwatchLogs
    {
        $result = $this->find($ptr, $columns);

        if ($result === null) {
            throw (new LogNotFoundException)->setModel(
                get_class($this->model), $ptr
            );
        }

        return $result;
    }

    /**
     * Save a new log and return the instance.
     *
     * @param array $attributes
     * @return AwsCloudwatchLogs
     */
    public function create(array $attributes = []): AwsCloudwatchLogs
    {
        $newModelInstance = $this->newModelInstance($attributes);

        return tap($newModelInstance, function (AwsCloudwatchLogs $instance) {
            $instance->save();
        });
    }

    /**
     * Insert new log into the aws cloudwatch logs stream.
     *
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

    /**
     * Build sql query string.
     *
     * @return string
     */
    private function buildQuery(): string
    {
        $wheres = collect($this->wheres)
            ->filter(function ($item) {
                return $item['column'] !== 'timestamp';
            })->toArray();

        $properties = [
            'select' => $this->columns,
            'wheres' => $wheres,
            'sorts' => $this->sorts,
            'limit' => $this->limit
        ];

        return (new QueryBuilder($this->model, $properties))->raw();
    }

    /**
     * Extract Time Range for get events log from AWS CloudWatch Logs.
     * Default is current day.
     *
     * @return array
     */
    private function extractTimeRange(): array
    {
        $range = collect($this->wheres)->filter(function ($where) {
            return $where['column'] === 'timestamp';
        })->first()['value'] ?? null;

        if($range === null) {
            return [
                now()->startOfDay()->timestamp * 1000,
                now()->endOfDay()->timestamp * 1000
            ];
        }

        return $range;
    }

    /**
     * Returns a list of CloudWatch Logs Insights queries.
     *
     * @return AwsCloudWatchLastQueriesCollection
     */
    public function queries(): AwsCloudWatchLastQueriesCollection
    {
        $queries = new AwsCloudWatchLastQueriesCollection(
            $this->client->describeQueries()
        );

        return $queries->parsed();
    }
}
