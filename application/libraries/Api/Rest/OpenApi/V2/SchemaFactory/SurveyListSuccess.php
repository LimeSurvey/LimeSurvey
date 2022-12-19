<?php

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

$survey = require(__DIR__ . '/survey.php');

return Schema::create()
    ->title('Survey List')
    ->description('Survey List')
    ->type(Schema::TYPE_OBJECT)
    ->properties(Schema::array('surveys')->items(
    AllOf::create()->schemas($survey)
    ));
