<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\JsonPatch\Patcher;
use LimeSurvey\Api\Command\V1\SurveyPatch\{
    OpHandler\OpHandlerQuestionGroupPropReplace,
    OpHandler\OpHandlerSurveyPropReplace
};

class PatcherSurvey extends Patcher
{
    public function __construct($surveyId)
    {
        $this->setParams([
            'surveyId' => $surveyId
        ]);
        $this->addOpHandler(new OpHandlerSurveyPropReplace);
        $this->addOpHandler(new OpHandlerQuestionGroupPropReplace);
    }
}

/*
[
    'property' => [
        'path' => '/$prop',
        'isProp' => true,
        'modelClass' => Survey::class
    ],
    'defaultLanguage' => [
        'path' => '/defaultLanguage',
        'modelClass' => null // not supported
    ],
    'languages' => [
        'path' => '/languages',
        'modelClass' => null // not supported
    ],
    'language' => [
        'path' => '/languages/$id',
        'modelClass' => null // not supported
    ],
    'questionGroups' => [
        'path' => '/questionGroups',
        'isCollection' => true,
        'modelClass' => QuestionGroup::class
    ],
    'questionGroup' => [
        'path' => '/questionGroups/$id',
        'momodelClasse' => QuestionGroup::class
    ],
    'questionGroupL10ns' => [
        'path' => '/questionGroups/$questionGroupId/l10ns',
        'isCollection' => true,
        'modelClass' => QuestionGroupL10n::class
    ],
    'questionGroupL10nsLang' => [
        'path' => '/questionGroups/$questionGroupId/l10ns/$id',
        'modelClass' => QuestionGroupL10n::class
    ],

    'questions' => [
        'path' => '/questionGroups/$questionGroupId/questions',
        'isCollection' => true,
        'modelClass' => Question::class
    ],
    'question' => [
        'path' => '/questionGroups/$questionGroupId/questions/$id',
        'modelClass' => Question::class
    ],
    'questionL10ns' => [
        'path' => '/questionGroups/$questionGroupId/questions/$id/l10ns',
        'isCollection' => true,
        'modelClass' => QuestionL10n::class
    ],
    'questionL10nsLang' => [
        'path' => '/questionGroups/$questionGroupId/questions/$questionId/l10ns/$id',
        'modelClass' => QuestionL10n::class
    ],
    'questionAttributes' => [
        'path' => '/questionGroups/$questionGroupId/questions/$id/attributes',
        'isCollection' => true,
        'modelClass' => QuestionAttribute::class
    ],
    'questionAttribute' => [
        'path' =>'/questionGroups/$questionGroupId/questions/$questionId/attributes/$id',
        'modelClass' => QuestionAttribute::class
    ],

    'subquestions' => [
        'path' => '/questionGroups/$questionGroupId/questions/$id/subquestions',
        'isCollection' => true,
        'modelClass' => Question::class
    ],
    'subquestion' => [
        'path' => '/questionGroups/$questionGroupId/questions/$questionId'
            . '/subquestions/$id',
        'modelClass' => Question::class
    ],
    'subquestionL10ns' => [
        'path' => '/questionGroups/$questionGroupId/questions/$questionId'
            . '/subquestions/$id/l10ns',
        'isCollection' => true,
        'modelClass' => QuestionL10n::class
    ],
    'subquestionL10nsLang' => [
        'path' => '/questionGroups/$questionGroupId/questions/$questionId'
            . '/subquestions/$subquestionId/l10ns/$id',
        'modelClass' => QuestionL10n::class
    ],
    'subquestionAttributes' => [
        'path' => '/questionGroups/$questionGroupId/questions/$questionId'
            . '/subquestions/$id/attributes',
        'isCollection' => true,
        'modelClass' => QuestionAttribute::class
    ],
    'subquestionAttribute' => [
        'path' => '/questionGroups/$questionGroupId/questions/$questionId'
            . '/subquestions/$subquestionId/attributes/$id',
        'modelClass' => QuestionAttribute::class
    ]
]
*/
