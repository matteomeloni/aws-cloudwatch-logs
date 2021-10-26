<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Matteomeloni\AwsCloudwatchLogs\Traits\HasCloudWatchLogsInsight;

class Aggregates
{
    use HasCloudWatchLogsInsight;

    /**
     * @var float|int
     */
    protected $value;

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
        $function = $this->getFunction(array_key_first($results));
        $value = Arr::first($results);

        switch ($function) {
            case 'count':
            case 'min':
            case 'max':
                $this->value = $value;
                break;
        }
    }

    /**
     * @param $rawFunction
     * @return string
     */
    private function getFunction($rawFunction): string
    {
        return (string) Str::of($rawFunction)->replaceMatches('/\(.+\)/', '');
    }
}
