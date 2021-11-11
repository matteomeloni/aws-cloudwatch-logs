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

    /**
     * @var array
     */
    private array $groups;

    /**
     * @var array|mixed
     */
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
        $this->groups = $properties['groups'] ?? [];
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
        $this->query .= $this->parseWheres($this->wheres);
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

        return "fields " . implode(', ', $this->fields);
    }

    /**
     * @param $wheres
     * @param bool $nested
     * @return string|null
     */
    private function parseWheres($wheres, bool $nested = false): ?string
    {
        if (empty($wheres)) {
            return null;
        }

        $filter = ($nested === false) ? "| filter " : "";

        foreach ($wheres as $index => $where) {
            if(Arr::exists($where, 'nested')){
                $filter .= " {$where['boolean']} (" . $this->parseWheres($where['nested'], true) . ')';
            } else {
                $filter .= $this->getWhereStatement($where, $index === array_key_first($wheres));
            }
        }

        return $filter;
    }

    /**
     * @param array $where
     * @param bool $isFirst
     * @return string
     */
    private function getWhereStatement(array $where, bool $isFirst): string
    {
        $statement = '';

        if (!$isFirst) {
            $statement .= " {$where['boolean']} ";
        }

        if (in_array($where['operator'], ['=', '!=', '>', '>=', '<', '<='])) {
            $statement .= "{$where['column']} {$where['operator']} '{$where['value']}'";
        }

        if (in_array($where['operator'], ['in', 'not in'])) {
            $statement .= "{$where['column']} {$where['operator']}" . json_encode($where['value']);
        }

        if ($where['operator'] === 'between') {
            [$min, $max] = $where['value'];

            $statement .= "({$where['column']} <= {$min} and {$where['column']} >= {$max})";
        }

        if (in_array($where['operator'], ['like', 'not like'])) {
            $statement .= "{$where['column']} {$where['operator']} /{$where['value']}/";
        }

        if (in_array($where['operator'], ['isempty', 'not isempty'])) {
            $statement .= "{$where['operator']}({$where['column']})";
        }

        return $statement;
    }

    /**
     * @return string|null
     */
    private function parseStats(): ?string
    {
        if (empty($this->stats)) {
            return null;
        }

        $filter = "| stats {$this->stats['function']}({$this->stats['column']})";

        if(!empty($this->groups)) {
            $filter .= ' by ' . implode(', ', $this->groups);
        }

        return $filter;
    }

    /**
     * @return string|null
     */
    private function parseSorts(): ?string
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
                $sort .= ", ";
            }
        }

        return $sort;
    }

}
