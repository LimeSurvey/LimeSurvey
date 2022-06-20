<?php

declare(strict_types=1);

use Cycle\Schema\Generator;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Yii\Cycle\Command\Schema;
use Yiisoft\Yii\Cycle\Command\Migration;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\SchemaProviderInterface;
use Yiisoft\Yii\Middleware\SubFolder;
use Yiisoft\Db\Connection\Dsn;

return [
    'supportEmail' => 'support@example.com',
    'middlewares' => [
        ErrorCatcher::class,
        SubFolder::class,
        Router::class,
    ],

    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__)."/..",
            '@assets' => '@public/assets',
            '@assetsUrl' => '@baseUrl/assets',
            '@baseUrl' => '/',
            '@data' => '@root/data',
            '@public' => '@root/public',
            '@resources' => '@root/resources',
            '@runtime' => '@root/runtime',
            '@src' => '@root/src',
            '@tests' => '@root/tests',
            '@views' => '@root/views',
            '@vendor' => '@root/vendor',
        ],
    ],

    'yiisoft/router-fastroute' => [
        'enableCache' => false,
    ],

    'yiisoft/view' => [
        'basePath' => '@views',
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
        ],
    ],

    // Console commands
    'yiisoft/yii-console' => [
        'commands' => [
            'cycle/schema' => Schema\SchemaCommand::class,
            'cycle/schema/php' => Schema\SchemaPhpCommand::class,
            'cycle/schema/clear' => Schema\SchemaClearCommand::class,
            'cycle/schema/rebuild' => Schema\SchemaRebuildCommand::class,
            'migrate/create' => Migration\CreateCommand::class,
            'migrate/generate' => Migration\GenerateCommand::class,
            'migrate/up' => Migration\UpCommand::class,
            'migrate/down' => Migration\DownCommand::class,
            'migrate/list' => Migration\ListCommand::class,
        ],
    ],

    'yiisoft/db-mysql' => [
        'account' => [
            'dsn' => (new Dsn('mysql', 'db', 'c1accountstage', '3306'))->asString(),
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8'
        ],
        'comfortupdate' => [
            'dsn' => (new Dsn('mysql', 'db', 'limesurvey_comfortupdate', '3306'))->asString(),
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8'
        ]
    ],

    'yiisoft/yii-cycle' => [
        // DBAL config
        'dbal' => [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            'query-logger' => null,
            // Default database
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'sqlite'],
            ],
            'connections' => [
                'sqlite' => [
                    'driver' => SQLiteDriver::class,
                    'connection' => $_ENV['YII_ENV'] === 'production'
                        ? 'sqlite:@data/db/database.db'
                        : 'sqlite:@tests/_data/database.db',
                    'username' => '',
                    'password' => '',
                ],
            ],
        ],

        // Cycle migration config
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'App\\Migration',
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * SchemaProvider list for {@see \Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaProviderPipeline}
         * Array of classname and {@see SchemaProviderInterface} object.
         * You can configure providers if you pass classname as key and parameters as array:
         * [
         *     SimpleCacheSchemaProvider::class => [
         *         'key' => 'my-custom-cache-key'
         *     ],
         *     FromFilesSchemaProvider::class => [
         *         'files' => ['@runtime/cycle-schema.php']
         *     ],
         *     FromConveyorSchemaProvider::class => [
         *         'generators' => [
         *              Generator\SyncTables::class, // sync table changes to database
         *          ]
         *     ],
         * ]
         */
        'schema-providers' => [
            // Uncomment next line to enable schema cache
            // SimpleCacheSchemaProvider::class => ['key' => 'cycle-orm-cache-key'],
            FromConveyorSchemaProvider::class => [
                'generators' => [
                    Generator\SyncTables::class,
                ],
            ],
        ],

        /**
         * Config for {@see \Yiisoft\Yii\Cycle\Schema\Conveyor\AnnotatedSchemaConveyor}
         * Annotated entity directories list.
         * {@see \Yiisoft\Aliases\Aliases} are also supported.
         */
        'annotated-entity-paths' => [
            '@src',
        ],
    ],
    'yiisoft/yii-swagger' => [
        'annotation-paths' => [
            '@src',
        ],
    ],
];
