<?php

namespace Matteomeloni\AwsCloudwatchLogs\Collections;

use Illuminate\Support\Collection;
use Matteomeloni\AwsCloudwatchLogs\Traits\HasCloudWatchLogsInsight;

class LogsCollection extends Collection
{
    use HasCloudWatchLogsInsight;
}
