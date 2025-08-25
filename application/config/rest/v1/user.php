<?php

use LimeSurvey\Libraries\Api\Command\V1\UserDetail;
use LimeSurvey\Libraries\Api\Command\V1\UserSettingGetValue;
use LimeSurvey\Libraries\Api\Command\V1\UserSettingSetValue;
use LimeSurvey\Api\Command\V1\{
  UserList
};

use LimeSurvey\Api\Rest\V1\SchemaFactory\{SchemaFactoryError, SchemaFactoryUser, SchemaFactoryUserList, SchemaFactoryUserSetting};

$errorSchema = (new SchemaFactoryError)->make();

$rest = [];

$rest['v1/user'] = [
  'GET' => [
    'description' => 'User list',
    'commandClass' => UserList::class,
    'auth' => true,
    'params' => [
      'pageSize' => ['type' => 'int'],
      'page' => ['type' => 'int']
    ],
    'responses' => [
      'success' => [
        'code' => 200,
        'description' => 'Success',
        'content' => null,
        'schema' => (new SchemaFactoryUserList)->make()
      ],
      'unauthorized' => [
        'code' => 401,
        'description' => 'Unauthorized',
        'schema' => $errorSchema
      ],
      'forbidden' => [
        'code' => 403,
        'description' => 'Forbidden',
        'schema' => $errorSchema
      ]
    ]
  ]
];

$rest['v1/user-detail/$id'] = [
  'GET' => [
    'tag' => 'user',
    'description' => 'User detail',
    'commandClass' => UserDetail::class,
    'auth' => true,
    'responses' => [
      'success' => [
        'code' => 200,
        'description' => 'Success',
        'content' => null,
        'schema' => (new SchemaFactoryUser())->make()
      ],
      'unauthorized' => [
        'code' => 401,
        'description' => 'Unauthorized',
        'schema' => $errorSchema
      ],
      'not-found' => [
        'code' => 404,
        'description' => 'Not Found',
        'schema' => $errorSchema
      ]
    ]
  ]
];

$rest['v1/user-setting/$id'] = [
  'GET' => [
    'tag' => 'user-setting',
    'description' => 'User setting',
    'commandClass' => UserSettingGetValue::class,
    'auth' => true,
    'responses' => [
      'success' => [
        'code' => 200,
        'description' => 'Success',
        'content' => null,
        'schema' => (new SchemaFactoryUserSetting())->make()
      ],
      'unauthorized' => [
        'code' => 401,
        'description' => 'Unauthorized',
        'schema' => $errorSchema
      ],
      'not-found' => [
        'code' => 404,
        'description' => 'Not Found',
        'schema' => $errorSchema
      ]
    ]
  ]
];

$rest['v1/user-setting/$id'] = [
  'POST' => [
    'tag' => 'user-setting',
    'description' => 'User setting',
    'commandClass' => UserSettingSetValue::class,
    'auth' => true,
    'responses' => [
      'success' => [
        'code' => 200,
        'description' => 'Success',
        'content' => null,
        'schema' => (new SchemaFactoryUserSetting())->make()
      ],
      'unauthorized' => [
        'code' => 401,
        'description' => 'Unauthorized',
        'schema' => $errorSchema
      ],
      'not-found' => [
        'code' => 404,
        'description' => 'Not Found',
        'schema' => $errorSchema
      ]
    ]
  ]
];

return $rest;
