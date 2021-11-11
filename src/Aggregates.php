<?php

namespace Matteomeloni\CloudwatchLogs;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Matteomeloni\CloudwatchLogs\Collections\AggregatesCollection;
use Matteomeloni\CloudwatchLogs\Traits\HasCloudWatchLogsInsight;

class Aggregates
{
    use HasCloudWatchLogsInsight;

    /**
     * @var float|int|AggregatesCollection
     */
    protected $value;

    /**
     * @var string
     */
    protected string $function;

    public function __construct($logResult)
    {
        $this->cloudWatchLogsInsightQueryId = $logResult['queryId'];

        $this->cloudWatchLogsInsightQueryStatus = $logResult['status'];

        $this->value = $this->extractValue($logResult['results']);

        $this->function = $this->extractFunction($logResult['results']);
    }

    /**
     * @return float|int
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @param $logResult
     * @return int|float|AggregatesCollection
     */
    private function extractValue($logResult)
    {
        if (count($logResult) === 1 ) {
            $logResult = Arr::first($logResult);

            return Arr::first($logResult) + 0;
        }

        return $this->makeCollection($logResult);
    }

    /**
     * @param $logResult
     * @return string
     */
    private function extractFunction($logResult): string
    {
        $rawFunction = array_key_first(Arr::first($logResult));

        return (string)Str::of($rawFunction)
            ->replaceMatches('/\(.+\)/', '');
    }

    /**
     * @param $values
     * @return AggregatesCollection
     */
    public function makeCollection($values): AggregatesCollection
    {
        return new AggregatesCollection($values);
    }
}
