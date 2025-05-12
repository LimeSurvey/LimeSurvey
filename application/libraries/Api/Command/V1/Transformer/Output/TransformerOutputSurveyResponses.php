<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurveyResponses extends TransformerOutputActiveRecord
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'startlanguage' => ['key' => 'language', 'type' => 'string'],
            'seed' => ['key' => 'seed'],
            'submitdate' => [
                'key' => 'submitDate',
                'formatter' => ['dateTimeToJson' => true]
            ],
            'startdate' => ['key' => 'startDate'],
            'datestamp' => ['key' => 'dateStamp'],
        ]);
    }

    /**
     * Transform
     *
     * Returns an array of entity references indexed by the specified key.
     *
     * @param ?mixed $data
     * @param ?mixed $options
     * @return array
     * @throws TransformerException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function transform($data = [], $options = []): array
    {
        if ($data !== null) {
            $data = $data->getData();
        }

        $responses = [];
        if ($data !== null) {
            foreach ($data as $surveyResponse) {
                $responses[] = $this->transformerResponseItem($surveyResponse);
            }
        }


        return $responses;
    }


    /**
     * Transforms survey menu items and puts them into the main survey menus,
     * organized by their unique names.
     * @param array $surveyResponse
     * @return array
     */
    private function transformerResponseItem($surveyResponse): array
    {
        $responses = [];
        foreach ($surveyResponse as $key => $value) {
            if (str_contains($key, 'X')) {
                list($survey, $group, $question) = explode("X", $key);
                $responses[$key] = [
                    "key" => $key,
                    "id" => $question,
                    "gid" => $group,
                    "sid" => $survey,
                    "value" => $value
                ];
            }
        }

        $surveyResponse = parent::transform($surveyResponse);
        $surveyResponse['completed'] = !empty($surveyResponse['submitDate']);
        $surveyResponse['answers'] = $responses;

        return $surveyResponse;
    }
}
