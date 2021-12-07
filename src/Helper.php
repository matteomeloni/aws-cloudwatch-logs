<?php

namespace Matteomeloni\CloudwatchLogs;

use Carbon\Carbon;
use DateTime;
use Exception;

class Helper
{
    /**
     * @param $date
     * @param int $precision
     * @return int
     * @throws Exception
     */
    public static function getTimestamp($date, int $precision = 3): int
    {
        if (is_int($date)) {
            $date = strlen((string)$date) === 10
                ? Carbon::createFromTimestamp($date)
                : Carbon::createFromTimestampMs($date);
        }

        return (new Carbon(new DateTime($date)))
            ->getPreciseTimestamp($precision);
    }
}
