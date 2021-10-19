<?php

namespace Matteomeloni\AwsCloudwatchLogs\Client;

use Illuminate\Support\Carbon;

class Analyzer
{
    /**
     * @var array
     */
    private array $log;

    /**
     * @param $log
     */
    public function __construct($log)
    {
        $this->log = $log;
    }

    /**
     * @return array
     */
    public function beautifyLog(): array
    {
        $attributes = $this->messageAnalyzer($this->log['message']);

        $attributes['timestamp'] = $this->extractDateTime($this->log['timestamp']);

        $attributes['ingestionTime'] = isset($this->log['ingestionTime'])
            ? $this->extractDateTime($this->log['ingestionTime'])
            : null;

        return $attributes;
    }

    /**
     * @param $message
     * @return array
     */
    private function messageAnalyzer($message): array
    {
        if ($this->isJson($message)) {
            $message = (array)json_decode($message);
        }

        return is_array($message)
            ? $message
            : ['message' => $message];
    }

    /**
     * @param $message
     * @return bool
     */
    private function isJson($message): bool
    {
        return is_string($message) &&
            is_array(json_decode($message, true)) &&
            (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @param $timestamp
     * @return string
     */
    private function extractDateTime($timestamp): string
    {
        $dateTime =  (is_int($timestamp))
            ? Carbon::createFromTimestampMs($timestamp)
            : Carbon::createFromFormat('Y-m-d H:i:s.u', $timestamp);

        return $dateTime->format('Y-m-d H:i:s');
    }
}
