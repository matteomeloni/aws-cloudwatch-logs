# AwsCloudwatchLogs

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

---

Via Composer

```bash
$ composer require matteomeloni/aws-cloudwatch-logs
```

Run

```bash
$ php artisan vendor:publish --provider 'Matteomeloni\AwsCloudwatchLogs\AwsCloudwatchLogsServiceProvider'
```

Update AWS CloudWatch Logs config in `config/aws-cloudwatch-logs.php`

## Usage

---

Extends your model with `Matteomeloni\AwsCloudwatchLogs\CloudWatchLogs`

```php
use Matteomeloni\AwsCloudwatchLogs\CloudWatchLogs;

class Log extends CloudWatchLogs
{
    protected string $logGroupName = 'LOG GROUP NAME';
    protected string $logStreamName = 'LOG STREAM NAME';
}
```

### Retrieving Models

The model's `all` method will retrieve all of the logs from the model's associated logGroupName and logStreamName.

By default, all logs generated on the current day will be extracted.

If you want to change the time interval, then just use the `whereBetween` method

```php
use App\Models\Log;

// All logs generated on the current day...
foreach(Logs::all() as $log) {
    echo $log->attributeOne;
}

// All logs generated on custom time interval...
$logs = Logs::whereBetween('timestamp', [$start, $end])->get();
```

#### Building Queris

This package support the "CloudWatch Logs Insights" feature.

For more information of this feature, you can see the [Aws CloudWatch Logs Insigiht Documentation](https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/AnalyzingLogData.html)

To start a new query you can use the `query` method and the result of this operation is the `queryId` string, to be used to retrieve the query results:

```php
use App\Models\Log;

// Start a new query and retrieve the queryId string... 
$queryId = Log::query()
    ->where('foo','bar')
    ->get();

// Retrieve the query results... 
$logs = Log::query($queryId)->get();


// Retrieve last started query...
$logs = Log::queries();
```

##### Conditions

Avaialable comparison operators: `=` `!=` `<` `<=` `>` `>=`

```php
use App\Models\Log;

// Chainable for 'AND'.
Log::query()
    ->where('column', 'operator', 'value')
    ->where('column', 'operator', 'value')
    ->get();

// Chainable for 'OR'.
Log::query()
    ->where('column', 'operator', 'value')
    ->orWhere('column', 'operator', 'value')
    ->get();

// Other types of conditions
Log::whereIn('column', [1, 2, 3])->get();
Log::orWhereIn('column', [1, 2, 3])->get();

Log::whereNotIn('column', [1, 2, 3])->get();
Log::orWhereNotIn('column', [1, 2, 3])->get();

Log::whereBetween('column', [1, 100])->get();
Log::orWhereBetween('column', [1, 100])->get();

Log::whereNull('column')->get();
Log::orWhereNull('column')->get();

Log::whereNotNull('column')->get();
Log::orWhereNotNull('column')->get();
```

### Retrieving Single Model

In addition to retrieving all of the records matching a given query, you may also retrieve single records using the `find` or `first` methods.

```php
use App\Models\Log;

/**
 * Retrieve a log by its logRecordPointer 
 * (you can retrieve that from the response of insight query)...
 */
$log = Log::find($ptr);

// Retrieve the first model matching the query constraints...
$log = Log::all()->first();
```

### Store new log

To insert a new record into AWS CloudWatch Log, you should instantiate a new model instance and set attributes on the model.

Then, call the save method on the model instance:

```php
use App\Models\Log;

$log = new Log();
$log->attributeOne = 'foo';
$log->attributeTwo = 'bar';
$log->save();
```

Alternatively, you may use the create method to "save" a new log using a single PHP statement.
The inserted log instance will be returned to you by the create method:

```php
use App\Models\Log;

$log = Log::create([
    'attributeOne' => 'foo',
    'attributeTwo' => 'bar'
]);
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author@email.com instead of using the issue tracker.

## Credits

- [Matteo Meloni][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/matteomeloni/aws-cloudwatch-logs.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/matteomeloni/aws-cloudwatch-logs.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/matteomeloni/aws-cloudwatch-logs
[link-downloads]: https://packagist.org/packages/matteomeloni/aws-cloudwatch-logs
[link-author]: https://github.com/matteomeloni
[link-contributors]: ../../contributors
