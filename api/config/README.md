For local db config create file config/params-local.php

and put inside following code:

```php
<?php

declare(strict_types=1);

use Yiisoft\Db\Connection\Dsn;

return [
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
    ]
];
```
Now adjust the credentials to your needs.