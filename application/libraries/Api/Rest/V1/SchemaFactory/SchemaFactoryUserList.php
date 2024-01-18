<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;

class SchemaFactoryUserList
{
    public function make(): Schema
    {
        return Schema::create()
            ->title('User List')
            ->description('User List')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::array('users')->items(
                    AllOf::create()->schemas(
                        (new SchemaFactoryUser())->make()
                    )
                )
            );
    }
}
