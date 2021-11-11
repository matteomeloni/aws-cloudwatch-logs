<?php

namespace Unit;

use Matteomeloni\CloudwatchLogs\Aggregates;
use Matteomeloni\CloudwatchLogs\Collections\AggregatesCollection;
use Matteomeloni\CloudwatchLogs\Tests\TestCase;

class AggregatesTest extends TestCase
{
    /** @test */
    public function if_query_not_contains_grouping_statement_then_return_single_number_value()
    {
        $result = [
            'queryId' => 'queryId',
            'status' => 'Complete',
            'results' => [['count()' => "6"]]
        ];

        $aggregates = new Aggregates($result);

        $this->assertEquals(6, $aggregates->get());
    }

    /** @test */
    public function if_query_contains_grouping_statement_then_return_aggregates_collection()
    {
        $result = [
            'queryId' => 'queryId',
            'status' => 'Complete',
            'results' => [
                ['col1' => 'value1', 'count()' => "6"],
                ['col2' => 'valu2', 'count()' => "3"],
            ]
        ];

        $aggregates = (new Aggregates($result))->get();

        $this->assertInstanceOf(AggregatesCollection::class, $aggregates);
        $this->assertEquals(2, $aggregates->count());
        $this->assertEquals(['col1' => 'value1', 'count()' => "6"], $aggregates->first());
    }
}
