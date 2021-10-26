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

    /**
     * @var string
     */
    protected string $function;

    public function __construct($logResult)
    {
        $this->cloudWatchLogsInsightQueryId = $logResult['queryId'];

        $this->cloudWatchLogsInsightQueryStatus = $logResult['status'];

        $this->value = $this->extractValue($logResult);

        $this->function = $this->extractFunction($logResult);
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
     * @return int|float
     */
    private function extractValue($logResult)
    {
        $logResult = Arr::first($logResult['results']);

        return Arr::first($logResult) + 0;
    }

    /**
     * @param $logResult
     * @return string
     */
    private function extractFunction($logResult): string
    {
        $rawFunction = array_key_first(Arr::first($logResult['results']));

        return (string) Str::of($rawFunction)
            ->replaceMatches('/\(.+\)/', '');
    }
}
