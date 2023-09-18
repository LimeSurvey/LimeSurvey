<?php

namespace LimeSurvey\Api\Command\V1\Transformer\Input;

use LimeSurvey\Api\Transformer\Transformer;

/**
 * Class TransformerInputSubQuestion
 * Although subquestions are handled as questions from the db, the expected
 * structure by the service is totally different.
 */
class TransformerInputSubQuestion extends Transformer
{
    public function __construct()
    {
        $this->setDataMap([
            'oldCode' => 'oldcode',
            'title' => 'code',
            'relevance' => true,
            'questionOrder' => ['key' => 'question_order', 'type' => 'int'],
            'sortOrder' => ['key' => 'question_order', 'type' => 'int']
        ]);
    }
}
