# Yii1 Db Seeder

Yii2 Db seeder is a Yii1 adapter for [Php db seeder](http://github.com/tebazil/db-seeder), which is a tool to quickly populate database with fake data.


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require tebazil/yii1-db-seeder
```

or add

```
"tebazil/yii1-db-seeder": "*"
```

to the `require` section of your `composer.json` file.

## Quick start

You can quickly seed your database with generated data with something like this:

```php
$seeder = new \tebazil\yii1seeder\Seeder();
$generator = $seeder->getGeneratorConfigurator();
$faker = $generator->getFakerConfigurator();

$seeder->table('article')->columns([
    'id', //automatic pk
    'book_id', //automatic fk
    'name'=>$faker->firstName,
    'content'=>$faker->text
        ])->rowQuantity(30);


$seeder->table('book')->columns([
    'id',
    'name'=>$faker->text(20),
])->rowQuantity(30);

$seeder->table('category')->columns([
    'id',
    'book_id',
    'name'=>$faker->text(20),
    'type'=>$faker->randomElement(['shop','cv','test']),
])->rowQuantity(30);

$seeder->refill();
```

Please review the [Php db seeder](http://github.com/tebazil/db-seeder) for extended documentation on usage.

## Differences from original library
- With `Yii1 Db Seeder` you do not need to provide database configuration by default, it uses `Yii::app()->db` to retrieve connection information. 
You can optionally provide seeder with a database connection component name. You do that like:

```php
$seeder = new \tebazil\yii1seeder\Seeder('db2');
```

There. Seeder would be using `Yii::app()->db2` now.

- You can use standart yii wildcards for prefixed table names, like `{{table_name}}`.

- Yii1's `Migration` class is used as a database abstraction layer instead of a native database helper. This basically means that you can work with all the databases Yii1 supports. 

## Best usage practices

- Create your project's database, generate some models and crud with gii.
- Make a console application controller for action refilling database with test data.
- Run it when it is needed

=> You have project with test data in your database, easier to develop and easier to show to clients what's being happening

* * * 

 - If you use migrations, don't be confused with the fact that migrations keep the project history, and `db-seeder` is not bound to them - migrations and seeding are two different instruments. Migrations is a long-term instrument to manipulate database structure with time keeping history, and database seeder is a simple library to keep test data up-to-date. So, when a new migration appears, just fix the console command filling the database to be up-to-date.
 - On early project develoment stages, the tool is useful when being run every time a commit happens, happening immediately after migrations are applied
 - On later project development stages constant test data refilling might ruin the testing, so the advice would be to refill manually and only the tables you need (usually the newly created ones - to test out new things)  



