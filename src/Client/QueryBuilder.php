<?php

namespace Matteomeloni\CloudwatchLogs\Client;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Matteomeloni\CloudwatchLogs\CloudWatchLogs;

class QueryBuilder
{
    /**
     * @var CloudWatchLogs
     */
    private CloudWatchLogs $model;

    /**
     * @var array
     */
    private array $fields;

    /**
     * @var array
     */
    private array $wheres;

    private array $stats;

    /**
     * @var array
     */
    private array $sorts;

    /**
     * @var int|null
     */
    private ?int $limit;

    /**
     * @var string
     */
    private string $query = '';

    /**
     * Create new Query Builder instance.
     *
     * @param CloudWatchLogs $model
     * @param array $properties
     */
    public function __construct(CloudWatchLogs $model, array $properties = [])
    {
        $this->model = $model;
        $this->fields = $properties['select'] ?? [];
        $this->wheres = $properties['wheres'] ?? [];
        $this->stats = $properties['stats'] ?? [];
        $this->sorts = $properties['sorts'] ?? [];
        $this->limit = $properties['limit'] ?? null;
    }

    /**
     * Return sql query string.
     *
     * @return string
     */
    public function raw(): string
    {
        $this->query .= $this->parseFields();
        $this->query .= $this->setLogStream();
        $this->query .= $this->parseWheres();
        $this->query .= $this->parseStats();
        $this->query .= $this->parseSorts();

        if ($this->limit) {
            $this->query .= "| limit {$this->limit}";
        }

        return Str::of($this->query)->replaceMatches("/(\s){2,}/", "");
    }

    /**
     * @return string
     */
    private function setLogStream(): string
    {
        return "| filter @logStream = '{$this->model->getLogStreamName()}'";
    }

    /**
     * @return string
     */
    private function parseFields(): string
    {
        if (in_array('*', $this->fields)) {
            return "fields @timestamp, @ingestionTime, @message";
        }

        if (!Arr::exists($this->fields, '@timestamp')) {
            $this->fields[] = '@timestamp';
        }

        if (!Arr::exists($this->fields, '@ingestionTime')) {
            $this->fields[] = '@ingestionTime';
        }

        return "fields " . implode(',', $this->fields);
    }

    /**
     * @return string|null
     */
    private function parseWheres(): ?string
    {
        if (empty($this->wheres)) {
            return null;
        }

        $filter = "| filter ";

        foreach ($this->wheres as $index => $where) {
            if ($index !== array_key_first($this->wheres)) {
                $filter .= " {$where['boolean']} ";
            }

            if (in_array($where['operator'], ['=', '!=', '>', '>=', '<', '<='])) {
                $filter .= "{$where['column']} {$where['operator']} '{$where['value']}'";
            }

            if (in_array($where['operator'], ['in', 'not in'])) {
                $filter .= "{$where['column']} {$where['operator']}" . json_encode($where['value']);
            }

            if ($where['operator'] === 'between') {
                [$min, $max] = $where['value'];

                $filter .= "({$where['column']} <= {$min} and {$where['column']} >= {$max})";
            }

            if (in_array($where['operator'], ['like', 'not like'])) {
                $filter .= "{$where['column']} {$where['operator']} /{$where['value']}/";
            }

            if (in_array($where['operator'], ['isempty', 'not isempty'])) {
                $filter .= "{$where['operator']}({$where['column']})";
            }
        }

        return $filter;
    }

    /**
     * @return string|null
     */
    public function parseStats(): ?string
    {
        if (empty($this->stats)) {
            return null;
        }

        $filter = "| stats ";
//        if ($this->stats['function'] === 'count') {
        $filter .= "{$this->stats['function']}({$this->stats['column']})";
//        }

//        if ($this->stats['function'] === 'min') {
//            $filter .= "{$this->stats['function']}({$this->stats['column']})";
//        }

        return $filter;
    }

    /**
     * @return string|null
     */
    public function parseSorts(): ?string
    {
        if (empty($this->sorts)) {
            return null;
        }

        $sort = "| sort ";

        foreach ($this->sorts as $index => $item) {
            $column = ($item['column'] === 'timestamp')
                ? "@timestamp"
                : $item['column'];

            $sort .= "{$column} {$item['direction']}";

            if ($index !== array_key_last($this->sorts)) {
                $sort .= " ,";
            }
        }

        return $sort;
    }
}
