<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryExpressionScriptValidation
{
    public function make(SchemaContract ...$properties): Schema
    {
        $diagnosticSchema = Schema::object()
            ->properties(
                Schema::integer('from')->default(0),
                Schema::integer('to')->default(0),
                Schema::string('severity'),
                Schema::string('message')
            );

        return Schema::create()
            ->title('Expression Script validation')
            ->description('Expression Manager validation diagnostics')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::boolean('valid'),
                Schema::array('diagnostics')->items($diagnosticSchema),
                ...$properties
            );
    }
}
