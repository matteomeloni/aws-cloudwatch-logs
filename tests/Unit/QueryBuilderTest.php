<?php

namespace Matteomeloni\CloudwatchLogs\Tests\Unit;

use Matteomeloni\CloudwatchLogs\Client\QueryBuilder;
use Matteomeloni\CloudwatchLogs\CloudWatchLogs;
use Matteomeloni\CloudwatchLogs\Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var CloudWatchLogs|\Matteomeloni\CloudwatchLogs\Tests\__anonymous|__anonymous@963
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->getFakeModel();
    }

    /** @test */
    public function if_is_set_select_statement_then_return_a_valid_query_string()
    {
        $properties = [
            'select' => ['col1', 'col2', 'col3']
        ];

        $queryBuilder = (new QueryBuilder($this->model, $properties))->raw();

        $string = "fields col1, col2, col3, @timestamp, @ingestionTime| filter @logStream = 'test'";
        $this->assertEquals($string, $queryBuilder);
    }

    /** @test */
    public function if_is_set_where_statement_then_return_a_valid_query_string()
    {
        $properties = [
            'select' => ['*'],
            'wheres' => [
                ['column' => 'foo', 'operator' => '=', 'value' => 'bar', 'boolean' => 'and'],
                ['column' => 'foo', 'operator' => '>', 'value' => 'bar', 'boolean' => 'or'],
                ['boolean' => 'and', 'nested' => [
                    ['column' => 'foo', 'operator' => '=', 'value' => 'bar', 'boolean' => 'and'],
                    ['column' => 'foo', 'operator' => '>', 'value' => 'bar', 'boolean' => 'or'],
                ]]
            ]
        ];

        $queryBuilder = (new QueryBuilder($this->model, $properties))->raw();

        $string = "fields @timestamp, @ingestionTime, @message| filter @logStream = 'test'";
        $string .= "| filter foo = 'bar' or foo > 'bar'";
        $string .= " and (foo = 'bar' or foo > 'bar')";

        $this->assertEquals($string, $queryBuilder);
    }

    /** @test */
    public function if_is_set_aggregate_statement_then_return_a_valid_query_string()
    {
        $properties = [
            'select' => ['*'],
            'stats' => [
                'function' => 'sum', 'column' => 'foo'
            ]
        ];

        $queryBuilder = (new QueryBuilder($this->model, $properties))->raw();

        $string = "fields @timestamp, @ingestionTime, @message| filter @logStream = 'test'";
        $string .= "| stats sum(foo)";

        $this->assertEquals($string, $queryBuilder);
    }

    /** @test */
    public function if_is_set_aggregate_with_grouping_statement_then_return_a_valid_query_string()
    {
        $properties = [
            'select' => ['*'],
            'stats' => [
                'function' => 'sum', 'column' => 'foo'
            ],
            'groups' => [
                'foo',
                'bin(1m)'
            ]
        ];

        $queryBuilder = (new QueryBuilder($this->model, $properties))->raw();

        $string = "fields @timestamp, @ingestionTime, @message| filter @logStream = 'test'";
        $string .= "| stats sum(foo) by foo, bin(1m)";

        $this->assertEquals($string, $queryBuilder);
    }

    /** @test */
    public function if_is_set_sort_statement_then_return_a_valid_query_string()
    {
        $properties = [
            'select' => ['*'],
            'sorts' => [
                ['column' => 'foo', 'direction' => 'asc'],
                ['column' => 'foo', 'direction' => 'desc']
            ]
        ];

        $queryBuilder = (new QueryBuilder($this->model, $properties))->raw();

        $string = "fields @timestamp, @ingestionTime, @message| filter @logStream = 'test'";
        $string .= "| sort foo asc, foo desc";

        $this->assertEquals($string, $queryBuilder);
    }

    /** @test */
    public function if_is_set_limit_statement_then_return_a_valid_query_string()
    {
        $properties = [
            'select' => ['*'],
            'limit' => 10
        ];

        $queryBuilder = (new QueryBuilder($this->model, $properties))->raw();

        $string = "fields @timestamp, @ingestionTime, @message| filter @logStream = 'test'";
        $string .= "| limit 10";

        $this->assertEquals($string, $queryBuilder);
    }
}
