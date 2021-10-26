<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\Arr;
use Matteomeloni\AwsCloudwatchLogs\Traits\HasCloudWatchLogsInsight;

class Aggregates
{
    use HasCloudWatchLogsInsight;

    /**
     * @var float|int
     */
    protected  $value;

    public function __construct($logResult)
    {
        $this->cloudWatchLogsInsightQueryId = $logResult['queryId'];
        $this->cloudWatchLogsInsightQueryStatus = $logResult['status'];

        $this->parseResults(Arr::first($logResult['results']));
    }

    /**
     * @return float|int
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @param $results
     */
    private function parseResults($results)
    {
        $function = Arr::first(array_keys($results));

        if (preg_match('/count/', $function)) {
            $this->value = (int) $results[$function] ;
        }
    }
}
