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

    private array $sorts;

    /**
     * @var string
     */
    private string $query = '';

    /**
     * @param AwsCloudwatchLogs $model
     * @param array $wheres
     * @param array $sorts
     */
    public function __construct(AwsCloudwatchLogs $model, array $wheres = [], array $sorts = [])
    {
        $this->model = $model;
        $this->wheres = $wheres;
        $this->sorts = $sorts;
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        $this->query .= $this->getBaseQuery();
        $this->query .= $this->parseWheres();
        $this->query .= $this->parseSorts();

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
     * @return string
     */
    private function parseWheres(): string
    {
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
     * @return string
     */
    public function parseSorts(): string
    {
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
