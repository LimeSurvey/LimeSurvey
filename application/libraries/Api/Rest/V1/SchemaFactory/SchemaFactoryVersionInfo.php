<?php

namespace LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;

class SchemaFactoryVersionInfo
{
    public function make(SchemaContract ...$properties): Schema
    {
        return Schema::create()->title('Version Info')
            ->description('Version Info')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::string('dbVersion')->default(null),
                Schema::string('assetsVersionNumber')->default(null),
            );
    }
}
