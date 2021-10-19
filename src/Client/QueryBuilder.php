<?php

namespace Matteomeloni\AwsCloudwatchLogs\Client;

use Illuminate\Support\Str;
use Matteomeloni\AwsCloudwatchLogs\AwsCloudwatchLogs;

class QueryBuilder
{
    /**
     * @var AwsCloudwatchLogs
     */
    private AwsCloudwatchLogs $model;

    /**
     * @var array
     */
    private array $wheres;

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
     * @param AwsCloudwatchLogs $model
     * @param array $properties
     */
    public function __construct(AwsCloudwatchLogs $model, array $properties = [])
    {
        $this->model = $model;
        $this->wheres = $properties['wheres'] ?? [];
        $this->sorts = $properties['sorts'] ?? [];
        $this->limit = $properties['limit'] ?? [];
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        $this->query .= $this->getBaseQuery();
        $this->query .= $this->parseWheres();
        $this->query .= $this->parseSorts();

        if($this->limit) {
            $this->query .= "| limit {$this->limit}";
        }

        return Str::of($this->query)->replaceMatches("/(\s){2,}/", "");
    }

    /**
     * @return string
     */
    private function getBaseQuery(): string
    {
        return "filter @logStream = '{$this->model->getLogStreamName()}'
            | fields @timestamp, @injectionTime, @message";
    }

    /**
     * @return string|null
     */
    private function parseWheres(): ?string
    {
        if(empty($this->wheres)) {
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
    public function parseSorts(): ?string
    {
        if(empty($this->sorts)) {
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
