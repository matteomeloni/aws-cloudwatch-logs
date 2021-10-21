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
     * Create a new Analyzer Instance.
     *
     * @param $log
     */
    public function __construct($log)
    {
        $this->log = $log;
    }

    /**
     * Return array that contains the log parsed and beautified.
     *
     * @param array $columns
     * @return array
     */
    public function beautifyLog(array $columns = ['*']): array
    {
        $attributes = (isset($this->log['message']))
            ? $this->messageAnalyzer($this->log['message'])
            : $this->log;

        if (!in_array('*', $columns)) {
            $attributes = collect($attributes)
                ->mapWithKeys(function ($value, $field) use ($columns) {
                    return in_array($field, $columns)
                        ? [$field => $value]
                        : null;
                })->toArray();
        }

        $attributes['ptr'] = $this->log['ptr'] ?? null;
        $attributes['timestamp'] = $this->extractDateTime($this->log['timestamp']);
        $attributes['ingestionTime'] = $this->extractDateTime($this->log['ingestionTime']);

        return $attributes;
    }

    /**
     * Parse the content of the message attribute.
     *
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
     * Check if the content of the attribute is a json.
     *
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
     * Return the parsed datetime attribute.
     * @param $timestamp
     * @return string|null
     */
    private function extractDateTime($timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        $dateTime = (is_int($timestamp))
            ? Carbon::createFromTimestampMs($timestamp)
            : Carbon::createFromFormat('Y-m-d H:i:s.u', $timestamp);

        return $dateTime->format('Y-m-d H:i:s');
    }
}
