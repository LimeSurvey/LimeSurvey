<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryPersonalSettings 
{    
    public function make(): Schema
    {
        $schema = new Schema();
        
        // Define your schema properties here
        // Example:
        $schema->title('Personal Settings')
            ->description('Personal Settings')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::string('answeroptionprefix')->default('A'),
                Schema::string('subquestionprefix')->default('SQ'),
                Schema::boolean('showQuestionCodes')->default(false)
            );
        
        return $schema;
    }
}