<?php

namespace Matteomeloni\CloudwatchLogs\Traits;

trait HasMessageParser
{
    /**
     * @param $data
     * @return string
     */
    private function getRawMessage($data): string
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        return $data;
    }
}
