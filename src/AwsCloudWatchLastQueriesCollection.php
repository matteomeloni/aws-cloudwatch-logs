<?php

namespace Matteomeloni\AwsCloudwatchLogs;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AwsCloudWatchLastQueriesCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    /**
     * @return AwsCloudWatchLastQueriesCollection
     */
    public function parsed(): AwsCloudWatchLastQueriesCollection
    {
        return $this->map(function ($query) {
            return [
                'queryId' => $query['queryId'],
                'queryString' => (string)Str::of($query['queryString'])->replace('|', "\n|"),
                'status' => $query['status'],
                'createTime' => Carbon::createFromTimestampMsUTC($query['createTime'])->format('Y-m-d H:i:s'),
            ];
        })->sortByDesc('createTime');
    }
}
