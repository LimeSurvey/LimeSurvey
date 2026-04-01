<?php

namespace LimeSurvey\Libraries\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactorySurveyResponsesExport
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function make(): Schema
    {
        return Schema::create()
            ->title('Survey Responses Export')
            ->description('Survey Responses Export')
            ->type(Schema::TYPE_STRING)
            ->format(Schema::FORMAT_BINARY);
    }
}
