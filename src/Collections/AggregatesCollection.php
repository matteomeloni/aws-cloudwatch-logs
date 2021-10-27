<?php

namespace Matteomeloni\CloudwatchLogs\Collections;

use Illuminate\Support\Collection;

class AggregatesCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
