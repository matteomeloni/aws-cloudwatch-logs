<?php

namespace Matteomeloni\CloudwatchLogs\Contracts;

interface ClientInterface
{
    /**
     * Lists log events from the specified log stream.
     * You can list all the log events or filter using a time range.
     *
     * @param array $timeRange
     * @param bool $startFromHead
     * @return array
     */
    public function getLogEvents(array $timeRange, bool $startFromHead): array;

    /**
     * Schedules a query of a log group using CloudWatch Logs Insights.
     * You specify the log group and time range to query and the query string to use.
     *
     * @param array $timeRange
     * @param string $query
     * @return string|null
     */
    public function startQuery(array $timeRange, string $query): ?string;

    /**
     * Returns the results from the specified query.
     * If the value of the Status field in the output is Running, this operation returns only partial results.
     * If you see a value of Scheduled or Running for the status, you can retry the operation later to see the final results.
     *
     * @param string $queryId
     * @return array
     */
    public function getQueryResults(string $queryId): array;

    /**
     * Returns a list of CloudWatch Logs Insights queries that are scheduled, executing, or have been executed recently in this account.
     *
     * @return array
     */
    public function describeQueries(): array;

    /**
     * Retrieves all the fields and values of a single log event
     *
     * @param string $ptr
     * @return array|null
     */
    public function getLogRecord(string $ptr): ?array;

    /**
     * Uploads a batch of log events to the specified log stream.
     *
     * @param array $data
     * @return bool
     */
    public function putLogEvents(array $data = []): bool;
}
