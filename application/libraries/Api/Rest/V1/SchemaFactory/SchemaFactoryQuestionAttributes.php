<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryQuestionAttributes
{
    /**
     * Create Schema
     *
     * @param string $key
     * @return Schema
     */
    public function make($key = '_attributeName'): Schema
    {
        return Schema::create($key)
            ->title('Question Attributes')
            ->description('Question Attributes')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::integer('qid'),
                (new SchemaFactoryQuestionAttribute())->make('_lang'),
                (new SchemaFactoryQuestionAttribute())->make('')
            );
    }
}
