<?php

namespace Matteomeloni\CloudwatchLogs\Collections;

use Illuminate\Support\Collection;
use Matteomeloni\CloudwatchLogs\Traits\HasCloudWatchLogsInsight;

class LogsCollection extends Collection
{
    use HasCloudWatchLogsInsight;

    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
