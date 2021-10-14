<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Illuminate\Support\Collection;

class AwsCloudWatchLogsCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
