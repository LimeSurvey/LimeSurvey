<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;

class TransformerOutputSurveyResponses extends TransformerOutputActiveRecord
{
    public array $fieldMap = [];

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setDataMap([
            'id' => ['type' => 'int'],
            'startlanguage' => ['key' => 'language', 'type' => 'string'],
            'seed' => ['key' => 'seed'],
            'lastpage' => ['key' => 'lastPage'],
            'submitdate' => [
                'key' => 'submitDate',
                'formatter' => ['dateTimeToJson' => true]
            ],
            'startdate' => ['key' => 'startDate'],
            'ipaddr' => ['key' => 'ipAddr'],
            'refurl' => ['key' => 'refUrl'],
            'datestamp' => ['key' => 'dateLastAction'],
            'token' => ['key' => 'token'],
            'firstname' => ['key' => 'firstName'],
            'lastname' => ['key' => 'lastName'],
            'email' => ['key' => 'email'],
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
        $firstName = '';
        $lastName = '';
        $email = '';

        try {
            if (is_object($surveyResponse)) {
                $firstName =  $surveyResponse->firstNameForGrid;
                $lastName = $surveyResponse->lastNameForGrid;
                $email = $surveyResponse->emailForGrid;
            }
        } catch (\Throwable $th) {
          //throw $th;
        }


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
            } elseif (str_starts_with($key, "Q") && !empty($this->fieldMap[$key])) {
                $responses[$key] = [
                    "key" => $key,
                    "id" => $this->fieldMap[$key]['qid'],
                    "gid" => $this->fieldMap[$key]['gid'],
                    "sid" => $this->fieldMap[$key]['sid'],
                    "value" => $value
                ];
            }
        }

        $surveyResponse = parent::transform($surveyResponse);
        $surveyResponse['completed'] = !empty($surveyResponse['submitDate']);

        if ($firstName) {
            $surveyResponse['firstName'] = $firstName;
        }
        if ($lastName) {
            $surveyResponse['lastName'] = $lastName;
        }
        if ($email) {
            $surveyResponse['email'] = $email;
        }

        $surveyResponse['answers'] = $responses;

        return $surveyResponse;
    }
}
