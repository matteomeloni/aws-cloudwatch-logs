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

    /**
     * @return AwsCloudWatchLastQueriesCollection
     */
    public function completed(): AwsCloudWatchLastQueriesCollection
    {
        return $this->addfilter('complete');
    }

    /**
     * @return AwsCloudWatchLastQueriesCollection
     */
    public function scheduled(): AwsCloudWatchLastQueriesCollection
    {
        return $this->addfilter('scheduled');
    }

    /**
     * @return AwsCloudWatchLastQueriesCollection
     */
    public function running(): AwsCloudWatchLastQueriesCollection
    {
        return $this->addfilter('running');
    }

    /**
     * @param $filter
     * @return AwsCloudWatchLastQueriesCollection
     */
    private function addfilter($filter): AwsCloudWatchLastQueriesCollection
    {
        return $this->parsed()->where('status', $filter);
    }
}
