<?php

namespace Matteomeloni\AwsCloudwatchLogs\Tests\Unit;

use Carbon\Carbon;
use Matteomeloni\AwsCloudwatchLogs\Client\Analyzer;
use Matteomeloni\AwsCloudwatchLogs\Tests\PackageTestCase;

class AnalyzerTest extends PackageTestCase
{
    private array $rawLog = [];
    private int $timestamp = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timestamp = Carbon::now()->timestamp * 1000;

        $this->rawLog = $this->createRawLog($this->timestamp);
    }

    /** @test */
    public function each_log_are_analyzed_and_beatified()
    {
        $analyzed = (new Analyzer($this->rawLog))->beautifyLog();

        $this->assertTrue(is_array($analyzed));

        $this->assertNotContains('message', $analyzed);

        $this->assertTrue($analyzed['timestamp'] === Carbon::createFromTimestampMs($this->timestamp)->format('Y-m-d H:i:s'));

        $this->assertTrue($analyzed['ingestionTime'] === Carbon::createFromTimestampMs($this->timestamp)->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function if_the_columns_attribute_is_passed_it_returns_only_the_specific_columns()
    {
        $analyzed = (new Analyzer($this->rawLog))
            ->beautifyLog([
                'level',
                'level_code'
            ]);

        $this->assertNotContains('environment', $analyzed);
        $this->assertNotContains('description', $analyzed);
    }
}
