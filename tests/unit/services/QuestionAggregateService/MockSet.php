<?php

namespace ls\tests\unit\services\QuestionAggregateService;

use Permission;
use Survey;
use CDbConnection;
use LimeSurvey\Models\Services\QuestionAggregateService\{
    SaveService,
    DeleteService
};

class MockSet
{
    public SaveService $saveService;
    public DeleteService $deleteService;
    public Permission $modelPermission;
    public Survey $modelSurvey;
    public CDbConnection $yiiDb;
}
