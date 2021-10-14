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

        $this->query = $this->getBaseQuery();
    }

    /**
     * @return string
     */
    public function raw(): string
    {
        foreach ($this->wheres as $index => $where) {
            if ($index !== array_key_first($this->wheres)) {
                $this->query .= " {$where['boolean']} ";
            }

            if (in_array($where['operator'], ['=', '!=', '>', '>=', '<', '<='])) {
                $this->query .= "{$where['column']} {$where['operator']} '{$where['value']}'";
            }

            if(in_array($where['operator'], ['in', 'not in'])) {
                $this->query .= "{$where['column']} {$where['operator']}" . json_encode($where['value']);
            }

            if(in_array($where['operator'], ['like', 'not like'])) {
                $this->query .= "{$where['column']} {$where['operator']} /{$where['value']}/";
            }

            if(in_array($where['operator'], ['isempty', 'not isempty'])) {
                $this->query .= "{$where['operator']}({$where['column']})";
            }

        }

        return Str::of($this->query)->replaceMatches("/(\s){2,}/", "");
    }

    /**
     * @return string
     */
    private function getBaseQuery(): string
    {
        return "filter @logStream = '{$this->model->getLogStreamName()}'
            | fields @timestamp, @injectionTime, @message
            | filter ";
    }

}
