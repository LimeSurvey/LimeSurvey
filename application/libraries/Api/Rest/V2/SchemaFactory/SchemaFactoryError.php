<?php

namespace LimeSurvey\Api\Rest\V2\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryError
{
    public function create() : Schema
    {
        return Schema::create()
            ->title('Error')
            ->description('Error')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::object('error')->properties(
                    Schema::string('code'),
                    Schema::string('message'),
                    Schema::object('data'),
                )
            );
    }
}
