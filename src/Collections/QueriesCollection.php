<?php

namespace Matteomeloni\CloudwatchLogs\Collections;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QueriesCollection extends Collection
{
    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    /**
     * @return QueriesCollection
     */
    public function parsed(): QueriesCollection
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
     * @return QueriesCollection
     */
    public function completed(): QueriesCollection
    {
        return $this->AddFilter('complete');
    }

    /**
     * @return QueriesCollection
     */
    public function scheduled(): QueriesCollection
    {
        return $this->AddFilter('scheduled');
    }

    /**
     * @return QueriesCollection
     */
    public function running(): QueriesCollection
    {
        return $this->AddFilter('running');
    }

    /**
     * @param $filter
     * @return QueriesCollection
     */
    private function AddFilter($filter): QueriesCollection
    {
        return $this->parsed()->where('status', $filter);
    }
}
