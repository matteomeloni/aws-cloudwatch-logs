# AwsCloudwatchLogs

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is where your description should go. Take a look at [contributing.md][link-contributors] to see a to do list.



* [Installation](#Installation)

* [Usage](#Usage)
  
  * [Retrieving Models](#retrieving-models)
    
    * [Building Queries](#building-queries)
      
      * [Select](#select)
      
      * [Conditions](#conditions)
      
      * [Ordering](#ordering)
      
      * [Limit](#limit)
      
      * [Aggregates](#aggregates)
      
      * [Retrieving Single Model](#retrieving-single-model)
  
  * [Retrieving Single Model](#retrieving-single-model)
  
  * [Store New Log](#store-new-log)
    
    

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
use Matteomeloni\CloudwatchLogs\CloudWatchLogs;

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

##### Select

The `select` method allows you to specify which columns to show in the query result.

```php
use App\Models\Log;

$logs = Log::query()
    ->select(['column1', 'column2'])
    ->get();


$logs = Log::query()
    ->select('column1', 'column2')
    ->get();
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

##### Ordering

The `orderBy` method allows sorting of the query by a given column.

The first argument accepted by this metod, should be the column you wish to sort by and the second argument, determines the direction of the sort, `asc` or `desc`

```php
use App\Models\Log;

//Ordering log by column asc...
Log::query()->orderBy('column')->get();

//Ordering log by column desc...
Log::query()->orderBy('column','desc')->get();

//Ordering log by column desc...
Log::query()->orderByDesc('column')->get();
```

##### Limit

The `take` or `limit`method allows to limit the number of results returned from the query.

```php
use App\Models\Log;

Log::query()->take(10)->get();

Log::query()->limit(10)->get();
```

##### Aggregates

This library also provides a variety of methods for retrieving aggregate values like `count`, `min`, `max`, `sum`, and `avg`. 

For more information of this feature, you can see the [Aws CloudWatch Logs Insigiht Documentation](https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/AnalyzingLogData.html)

You may call any of these methods after constructing your query:

```php
use App\Models\Log;

$count = Log::query()
    ->where('level_code', 500)
    ->count();

//Other types of aggregates
Log::query()->min('column');
Log::query()->max('column');
Log::query()->sum('column');
Log::query()->avg('column');
Log::query()->average('column'); //Alias for the "avg" method
```

It is possible to group and aggregate the results through the `groupBy` method. 

In this case the result of the operation will be a collection.

In addition to a specific column, it is also possible to group for all the functions offered by the AWS CloudWatch Logs Insights service.

```php
use App\Models\Log;


Log::query()
    ->groupBy('column')
    ->groupBy('bin (1m)')
    ->count();
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

// Find a model by its ptr or throw an exception...
$log = Log::findOrFail($ptr);

// Find multiple models by their ptr.
$logs = Log::findMany([$ptr1,$ptr2,$ptr3]);
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

Please see the [changelog][link-changelog] for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [contributing.md][link-contributors] for details and a todolist.

## Security

If you discover any security related issues, please email matteomelonig@gmail.com instead of using the issue tracker.

## Credits

- [Matteo Meloni][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File][link-license] for more information.

[ico-version]: https://img.shields.io/packagist/v/matteomeloni/aws-cloudwatch-logs.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/matteomeloni/aws-cloudwatch-logs.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/matteomeloni/aws-cloudwatch-logs
[link-downloads]: https://packagist.org/packages/matteomeloni/aws-cloudwatch-logs
[link-author]: https://github.com/matteomeloni
[link-contributors]: CONTRIBUTING.md
[link-changelog]: CHANGELOG.md
[link-license]: LICENSE.md
