<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryPermission
{
    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $properties
     */
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()->title('Permission')
            ->description('Permission')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::boolean('create'),
                Schema::boolean('read'),
                Schema::boolean('update'),
                Schema::boolean('delete'),
                Schema::boolean('import'),
                Schema::boolean('export'),
            );
    }
}
