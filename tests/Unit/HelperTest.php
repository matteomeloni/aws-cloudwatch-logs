<?php

namespace Matteomeloni\CloudwatchLogs\Tests\Unit;

use Illuminate\Support\Carbon;
use Matteomeloni\CloudwatchLogs\Helper;
use Matteomeloni\CloudwatchLogs\Tests\TestCase;

class HelperTest extends TestCase
{
    /** @test */
    public function if_passing_date_time_string_then_return_millisecond_timestamp()
    {
        $currentTimestampMs = (int) Carbon::createFromFormat('Y-m-d h:i:s', '2021-12-07 00:00:00')
            ->getPreciseTimestamp(3);

        $timestamp = Helper::getTimestamp('2021-12-07 00:00:00');
        $this->assertTrue($timestamp === $currentTimestampMs);

        $timestamp = Helper::getTimestamp('07-12-2021 00:00:00');
        $this->assertTrue($timestamp === $currentTimestampMs);

        $timestamp = Helper::getTimestamp('2021-12-07');
        $this->assertTrue($timestamp === $currentTimestampMs);

        $timestamp = Helper::getTimestamp('07-12-2021');
        $this->assertTrue($timestamp === $currentTimestampMs);
    }
}
