<?php

namespace Matteomeloni\CloudwatchLogs\Tests\Unit;

use Matteomeloni\CloudwatchLogs\Builder;
use Matteomeloni\CloudwatchLogs\Client\Mock\Client;
use Matteomeloni\CloudwatchLogs\CloudWatchLogs;
use Matteomeloni\CloudwatchLogs\Tests\TestCase;

class BuilderTest extends TestCase
{
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $fakeModel = new class extends CloudWatchLogs {
            protected string $logGroupName = 'Test';
            protected string $logStreamName = 'test';
        };

        $this->builder = new class($fakeModel) extends Builder {
            public function __construct(CloudWatchLogs $fakeModel)
            {
                $this->model = $fakeModel;
                $this->client = new Client($this->model->getLogGroupName(), $this->model->getLogStreamName());
            }
        };
    }

    /** @test */
    public function builder_is_belonged_to_cloudwatch_logs_model()
    {
        $this->assertInstanceOf(CloudWatchLogs::class, $this->builder->model);
    }

    /** @test */
    public function it_is_possible_start_a_insight_query()
    {
        $this->builder->query();

        $this->assertTrue($this->builder->useCloudWatchLogsInsight);
        $this->assertNull($this->builder->cloudWatchLogsInsightQueryId);
    }

    /** @test */
    public function it_is_possibile_to_set_the_list_of_columns()
    {
        $this->builder->select('col1', 'col2', 'col3');

        $this->assertTrue(in_array('col1', $this->builder->columns));
        $this->assertTrue(in_array('col2', $this->builder->columns));
        $this->assertTrue(in_array('col3', $this->builder->columns));

        $this->builder->select(['col1', 'col2', 'col3']);

        $this->assertTrue(in_array('col1', $this->builder->columns));
        $this->assertTrue(in_array('col2', $this->builder->columns));
        $this->assertTrue(in_array('col3', $this->builder->columns));
    }

    /** @test */
    public function it_is_possible_to_set_a_equal_condition()
    {
        $this->builder->where('foo', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '=',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[0]);

        $this->builder->where('foo', '=', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '=',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[1]);
    }

    /** @test */
    public function it_is_possible_to_set_a_not_equal_condition()
    {
        $this->builder->where('foo', '!=', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '!=',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_greater_than_condition()
    {
        $this->builder->where('foo', '>', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '>',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[0]);

        $this->builder->where('foo', '>=', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '>=',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[1]);
    }

    /** @test */
    public function it_is_possible_to_set_a_less_than_condition()
    {
        $this->builder->where('foo', '<', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '<',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[0]);

        $this->builder->where('foo', '<=', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '<=',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[1]);
    }

    /** @test */
    public function it_is_possible_to_set_a_contains_condition()
    {
        $this->builder->where('foo', 'like', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'like',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_not_contains_condition()
    {
        $this->builder->where('foo', 'not like', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'not like',
            'value' => 'bar',
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_between_condition()
    {
        $this->builder->whereBetween('foo', ['min', 'max']);

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'between',
            'value' => ['min', 'max'],
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_in_condition()
    {
        $this->builder->whereIn('foo', [1, 2]);

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'in',
            'value' => [1, 2],
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_not_in_condition()
    {
        $this->builder->whereNotIn('foo', [1, 2]);

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'not in',
            'value' => [1, 2],
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_null_condition()
    {
        $this->builder->whereNull('foo');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'isempty',
            'value' => null,
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_a_not_null_condition()
    {
        $this->builder->whereNotNull('foo');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => 'not isempty',
            'value' => null,
            'boolean' => 'and'
        ], $this->builder->wheres[0]);
    }

    /** @test */
    public function it_is_possible_to_set_or_condition()
    {
        $this->builder->where('foo', 'bar')
            ->orWhere('foo', 'bar');

        $this->assertEquals([
            'column' => 'foo',
            'operator' => '=',
            'value' => 'bar',
            'boolean' => 'or'
        ], $this->builder->wheres[1]);
    }

    /** @test */
    public function it_is_possible_to_set_group_by_condition()
    {
        $this->builder->groupBy('foo');

        $this->assertEquals('foo', $this->builder->groups[0]);
    }

    /** @test */
    public function it_is_possible_to_set_order_by_condition()
    {
        $this->builder->orderBy('foo');

        $this->assertEquals([
            'column' => 'foo',
            'direction' => 'asc'
        ], $this->builder->sorts[0]);
    }

    /** @test */
    public function it_is_possible_to_set_order_by_desc_condition()
    {
        $this->builder->orderBy('foo', 'desc');

        $this->assertEquals([
            'column' => 'foo',
            'direction' => 'desc'
        ], $this->builder->sorts[0]);

        $this->builder->orderByDesc('foo');

        $this->assertEquals([
            'column' => 'foo',
            'direction' => 'desc'
        ], $this->builder->sorts[1]);
    }

    /** @test */
    public function it_is_possible_to_set_a_limit_condition()
    {
        $this->builder->limit(10);

        $this->assertEquals(10, $this->builder->limit);
    }

    /** @test */
    public function it_is_possible_to_set_a_count_condition()
    {
        $this->builder->query()->count();

        $this->assertEquals([
            'function' => 'count',
            'column' => null
        ], $this->builder->aggregates);
    }

    /** @test */
    public function it_is_possible_to_set_a_sum_condition()
    {
        $this->builder->query()->sum('foo');

        $this->assertEquals([
            'function' => 'sum',
            'column' => 'foo'
        ], $this->builder->aggregates);
    }

    /** @test */
    public function it_is_possible_to_set_a_min_condition()
    {
        $this->builder->query()->min('foo');

        $this->assertEquals([
            'function' => 'min',
            'column' => 'foo'
        ], $this->builder->aggregates);
    }

    /** @test */
    public function it_is_possible_to_set_a_max_condition()
    {
        $this->builder->query()->max('foo');

        $this->assertEquals([
            'function' => 'max',
            'column' => 'foo'
        ], $this->builder->aggregates);
    }

    /** @test */
    public function it_is_possible_to_set_a_avg_condition()
    {
        $this->builder->query()->avg('foo');

        $this->assertEquals([
            'function' => 'avg',
            'column' => 'foo'
        ], $this->builder->aggregates);

        $this->builder->query()->average('foo');

        $this->assertEquals([
            'function' => 'avg',
            'column' => 'foo'
        ], $this->builder->aggregates);
    }
}
