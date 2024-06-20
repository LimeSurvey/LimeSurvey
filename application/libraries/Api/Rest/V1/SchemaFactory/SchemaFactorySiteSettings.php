<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactorySiteSettings
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()->title('Site Settings')
            ->description('Site Settings')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::string('siteName')->default(null),
                Schema::string('timezone')->default(null),
            );
    }
}
