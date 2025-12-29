<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;
use Survey;
use SurveyDynamic;

class TransformerOutputSurveyResponses extends TransformerOutputActiveRecord
{
    public array $fieldMap = [];

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setDataMap([
            'id'            => ['type' => 'int'],
            'startlanguage' => ['key' => 'language', 'type' => 'string'],
            'seed'          => ['key' => 'seed'],
            'lastpage'      => ['key' => 'lastPage'],
            'submitdate'    => [
                'key'       => 'submitDate',
                'formatter' => ['dateTimeToJson' => true]
            ],
            'startdate'     => ['key' => 'startDate'],
            'ipaddr'        => ['key' => 'ipAddr'],
            'refurl'        => ['key' => 'refUrl'],
            'datestamp'     => ['key' => 'dateLastAction'],
            'token'         => ['key' => 'token'],
            'firstname'     => ['key' => 'firstName'],
            'lastname'      => ['key' => 'lastName'],
            'email'         => ['key' => 'email'],
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
        $responses = [];
        if ($data !== null) {
            foreach ($data as $surveyResponse) {
                $responses[] = $this->transformerResponseItem($surveyResponse, $options ?? []);
            }
        }


        return $responses;
    }


    /**
     * Transforms survey menu items and puts them into the main survey menus,
     * organized by their unique names.
     * @param SurveyDynamic $surveyResponse
     * @param array $options
     * @return array
     * @throws TransformerException
     */
    private function transformerResponseItem(SurveyDynamic $surveyResponse, array $options): array
    {
        $surveyResponseAttributes = $surveyResponse->attributes;
        $responses = [];

        foreach ($surveyResponseAttributes as $key => $value) {
            if (str_contains($key, 'X')) {
                [$survey, $group, $question] = explode("X", $key);
                $responses[$key] = [
                    "key"   => $key,
                    "id"    => $question,
                    "gid"   => $group,
                    "sid"   => $survey,
                    "value" => $value
                ];
            } elseif (!empty($this->fieldMap[$key]) && str_starts_with($key, "Q")) {
                $responses[$key] = [
                    "key"   => $key,
                    "id"    => $this->fieldMap[$key]['qid'],
                    "gid"   => $this->fieldMap[$key]['gid'],
                    "sid"   => $this->fieldMap[$key]['sid'],
                    "value" => $value
                ];
            }
        }

        $surveyResponseArray = parent::transform($surveyResponse);
        if (!empty($options['survey'])) {
            /** @var Survey $survey */
            $survey = $options['survey'];
            $hasToken = $survey->anonymized === "N"
                && tableExists('tokens_' . $survey->sid)
                && isset($surveyResponse->tokens);
            if ($hasToken) {
                $surveyResponseArray['firstName'] = $surveyResponse->getFirstNameForGrid();
                $surveyResponseArray['lastName'] = $surveyResponse->getLastNameForGrid();
                $surveyResponseArray['email'] = $surveyResponse->getEmailForGrid();
            }
        }

        $surveyResponseArray['completed'] = !empty($surveyResponseArray['submitDate']);
        $surveyResponseArray['answers'] = $responses;

        // These values are used when datestamp setting is off.
        // We convert them to null for API consistency.
        foreach (['submitDate', 'dateLastAction', 'startDate'] as $dateField) {
            if (in_array($surveyResponseArray[$dateField], [
                '1980-01-01T00:00:00.000Z',
                '1980-01-01 00:00:00',
            ])) {
                $surveyResponseArray[$dateField] = null;
            }
        }

        return $surveyResponseArray;
    }
}
