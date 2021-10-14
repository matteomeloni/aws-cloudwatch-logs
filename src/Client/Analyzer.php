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
        $attributes['timestamps'] = Carbon::createFromTimestampMs($this->log['timestamp'])->format('Y-m-d H:i:s');
        $attributes['ingestionTime'] = Carbon::createFromTimestampMs($this->log['ingestionTime'])->format('Y-m-d H:i:s');

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
        return is_string($message) && is_array(json_decode($message, true)) && (json_last_error() == JSON_ERROR_NONE);
    }
}
