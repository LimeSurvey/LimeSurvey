<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;


class SchemaFactoryPersonalSettings 
{    
    public function make(SchemaContract ...$properties
    ): Schema
    {
        return Schema::create()->title('Personal Settings')
            ->description('Personal Settings')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::string('answeroptionprefix')->default('A'),
                Schema::string('subquestionprefix')->default('SQ'),
                Schema::boolean('showQuestionCodes')->default(false)
            );
    }
}