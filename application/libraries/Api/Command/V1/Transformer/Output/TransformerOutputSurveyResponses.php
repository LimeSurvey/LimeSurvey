<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputAnswer;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputAnswerL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputQuestion;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputQuestionAttribute;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputQuestionGroup;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputQuestionGroupL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputQuestionL10ns;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyGroup;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyMenuItems;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyMenus;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyOwner;
use Survey;
use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;
use SurveysGroups;
use DI\FactoryInterface;
use LimeSurvey\DI;

//use LimeSurvey\Models\Services\QuestionAggregateService\QuestionService;

/**
 * TransformerOutputSurveyDetail
 */
class TransformerOutputSurveyResponses extends TransformerOutputActiveRecord
{
    private TransformerOutputQuestion $transformerQuestion;
    private TransformerOutputQuestionL10ns $transformerQuestionL10ns;
    private TransformerOutputQuestionAttribute $transformerQuestionAttribute;
    private QuestionService $questionService;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'submitdate' => [
                'key' => 'submitDate',
                'formatter' => ['dateTimeToJson' => true]
            ],
            'startlanguage' => ['key' => 'language', 'type' => 'string']
        ]);
    }

    /**
     * Transform
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param ?mixed $data
     * @param ?mixed $options
     * @return ?mixed
     * @throws \LimeSurvey\Api\Transformer\TransformerException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function transform($data, $options = [])
    {
//        if (!$data instanceof \SurveyDynamic) {
//            return null;
//        }

        $data = $data->getData();
        $responses = [];
        foreach ($data as $surveyResponse) {
            $responses[] = $this->transformerResponseItem($surveyResponse);
        }

        return $responses;
    }


    /**
     * Transforms survey menu items and puts them into the main survey menus,
     * organized by their unique names.
     * @param array $surveyResponse
     * @return void
     */
    private function transformerResponseItem($surveyResponse): array
    {
        $responses = [];
        foreach ($surveyResponse as $key => $value) {
            if (str_contains($key, 'X')) {
                list($survey, $group, $question) = explode("X", $key);
                $responses[$question] = [
                    "question" => intval($question),
                    "group" => intval($group),
                    "value" => $value
                ];
            }
        }

        $surveyResponse = parent::transform($surveyResponse);
        $surveyResponse['answers'] = $responses;

        return $surveyResponse;
    }
}
