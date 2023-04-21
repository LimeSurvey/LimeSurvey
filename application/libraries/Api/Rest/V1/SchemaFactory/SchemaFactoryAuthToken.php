<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryAuthToken
{
    public function create(): Schema
    {
        return Schema::string()
            ->example('%7&!T%EYd@PnDB49MRfwQ!KjX48J^3x6rDhyB6DK');
    }
}
