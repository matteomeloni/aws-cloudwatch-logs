<?php

namespace Matteomeloni\CloudwatchLogs\Client\Mock;

use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Monolog\Logger;

class Factory
{
    /**
     * The current Faker instance.
     *
     * @var Generator
     */
    protected Generator $faker;

    /**
     * @var int
     */
    private int $items;

    /**
     * @param int $items
     * @throws BindingResolutionException
     */
    public function __construct(int $items = 1)
    {
        $this->faker = Container::getInstance()->make(Generator::class);
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function logs(): array
    {
        $logs = [];

        foreach (range(1, $this->items) as $item) {
            $logs[$item] = [
                'timestamp' => now()->getTimestampMs(),
                'message' => json_encode($this->definition()),
                'ingestionTime' => now()->getTimestampMs(),
                'ptr' => Str::random(117)
            ];
        }

        return $logs;
    }

    /**
     * @return array
     */
    public function queries(): array
    {
        $queries = [];

        foreach (range(1, $this->items) as $item) {
            $queries[$item] = [
                'queryId' => $this->faker->uuid(),
                'queryString' => "SOURCE \"Test\" START=1634812255000 END=1634860799000 | fields @timestamp, @ingestionTime, @message| filter @logStream = 'test'| filter level_code = '200'",
                'status' => "Complete",
                'createTime' => $this->faker->dateTimeThisMonth()->getTimestamp(),
                'logGroupName' => 'Test'
            ];
        }

        return $queries;
    }

    /**
     * @return array
     */
    private function definition(): array
    {
        $levelCode = $this->faker->randomElement(Logger::getLevels());

        return [
            'transactionId' => $this->faker->uuid(),
            'channel' => $this->faker->slug(),
            'environment' => 'develop',
            'level' => Logger::getLevelName($levelCode),
            'level_code' => $levelCode,
            'referer' => $this->faker->url(),
            'userAgent' => $this->faker->userAgent(),
            'browser' => null,
            'browserVersion' => null,
            'platform' => null,
            'platformVersion' => null,
            'ip' => $this->faker->ipv4(),
            'description' => $this->faker->sentence(),
            'details' => [],
            'trace' => [],
            'formatted' => null,
            'registeredAt' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
        ];
    }
}
