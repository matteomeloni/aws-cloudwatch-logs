<?php

namespace Matteomeloni\AwsCloudwatchLogs\Client;

use Illuminate\Support\Str;
use Matteomeloni\AwsCloudwatchLogs\AwsCloudwatchLogs;

class QueryBuilder
{
    private AwsCloudwatchLogs $model;

    private array $wheres;

    private string $query;

    /**
     * @param AwsCloudwatchLogs $model
     * @param array $wheres
     */
    public function __construct(AwsCloudwatchLogs $model, array $wheres = [])
    {
        $this->model = $model;
        $this->wheres = $wheres;
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        $this->query .= $this->getBaseQuery();
        $this->query .= $this->parseWheres();

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
}
