<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Permission;
use CDbConnection;
use LimeSurvey\Models\Services\QuestionAggregateService\SaveService;

class MockSet
{
    public SaveService $saveService;
    public Permission $modelPermission;
    public CDbConnection $yiiDb;
}
