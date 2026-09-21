<?php

namespace LimeSurvey\Libraries\Api\Command\V1\Transformer\Output;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Api\Transformer\Output\TransformerOutputActiveRecord;
use Survey;
use SurveyDynamic;

class TransformerOutputSurveyResponses extends TransformerOutputActiveRecord
{
    public array $fieldMap = [];

    /** @var bool|null Pre-cached token table existence check */
    public ?bool $hasTokenTable = null;

    /**
     * Ranking questions to fold out into one answer entry per rank position.
     * Keyed by the raw response attribute ("Q{qid}", the JSON-array column
     * actually stored in the database), each entry: ['qid', 'gid', 'sid',
     * 'columnCount']. Populated by
     * ExportSurveyResultsService::expandRankingFieldMap().
     *
     * @var array<string, array{qid: int, gid: int, sid: int, columnCount: int}>
     */
    public array $rankingFields = [];

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
            'quota_exit'    => ['key' => 'quotaExit', 'type' => 'int'],
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
     * Transforms a single survey response item into a structured array.
     * @param SurveyDynamic $surveyResponse
     * @param array $options
     * @return array
     * @throws TransformerException
     */
    private function transformerResponseItem(SurveyDynamic $surveyResponse, array $options): array
    {
        $surveyResponseArray = parent::transform($surveyResponse) ?? [];
        $this->applyTokenData($surveyResponseArray, $surveyResponse, $options);
        $quotaId = $surveyResponseArray['quotaExit'] ?? null;
        $surveyResponseArray['quotaExitName'] = $options['quotaNames'][(int) $quotaId]
            ?? null;
        $surveyResponseArray['completed'] = !empty($surveyResponseArray['submitDate']);
        $surveyResponseArray['answers'] = $this->extractAnswers($surveyResponse->attributes);
        $this->normalizeDateFields($surveyResponseArray);
        return $surveyResponseArray;
    }

    /**
     * Parses SGQA-keyed response attributes into structured answer entries.
     *
     * Ranking questions (listed in $this->rankingFields) are stored as a
     * single JSON array column (the ranked list of subquestion codes,
     * ordered by rank); those are decoded and folded out into one answer
     * entry per rank position instead of a single entry holding the raw
     * JSON, matching the columns registered by
     * ExportSurveyResultsService::expandRankingFieldMap().
     *
     * @param array $attributes
     * @return array
     */
    private function extractAnswers(array $attributes): array
    {
        $answers = [];
        foreach ($attributes as $key => $value) {
            if (str_contains($key, 'X')) {
                [$survey, $group, $question] = explode("X", $key);
                $answers[$key] = [
                    "key"   => $key,
                    "id"    => $question,
                    "gid"   => $group,
                    "sid"   => $survey,
                    "value" => $value
                ];
            } elseif (isset($this->rankingFields[$key])) {
                $ranking = $this->rankingFields[$key];
                $rankedCodes = json_decode((string) $value, true);
                for ($position = 1; $position <= $ranking['columnCount']; $position++) {
                    $rankFieldKey = "{$key}_rank{$position}";
                    $answers[$rankFieldKey] = [
                        "key"   => $rankFieldKey,
                        "id"    => $ranking['qid'],
                        "gid"   => $ranking['gid'],
                        "sid"   => $ranking['sid'],
                        "value" => (is_array($rankedCodes) && isset($rankedCodes[$position - 1])) ? $rankedCodes[$position - 1] : null,
                    ];
                }
            } elseif (!empty($this->fieldMap[$key]) && str_starts_with($key, "Q")) {
                $answers[$key] = [
                    "key"   => $key,
                    "id"    => $this->fieldMap[$key]['qid'],
                    "gid"   => $this->fieldMap[$key]['gid'],
                    "sid"   => $this->fieldMap[$key]['sid'],
                    "value" => $value
                ];
            }
        }
        return $answers;
    }

    /**
     * Appends token-related participant data if the survey is not anonymized.
     * @param array &$responseArray
     * @param SurveyDynamic $surveyResponse
     * @param array $options
     */
    private function applyTokenData(array &$responseArray, SurveyDynamic $surveyResponse, array $options): void
    {
        if (!empty($options['survey'])) {
            /** @var Survey $survey */
            $survey = $options['survey'];
            // Use cached value if available, otherwise check (for backward compatibility)
            $tokenTableExists = $this->hasTokenTable ?? tableExists('tokens_' . $survey->sid);
            $hasToken = $survey->anonymized === "N"
                && $tokenTableExists
                && isset($surveyResponse->tokens);
            if ($hasToken) {
                $responseArray['firstName'] = $surveyResponse->getFirstNameForGrid();
                $responseArray['lastName'] = $surveyResponse->getLastNameForGrid();
                $responseArray['email'] = $surveyResponse->getEmailForGrid();
            }
        }
    }

    /**
     * Converts placeholder dates to null for API consistency.
     * @param array &$responseArray
     */
    private function normalizeDateFields(array &$responseArray): void
    {
        foreach (['submitDate', 'dateLastAction', 'startDate'] as $dateField) {
            if (
                in_array($responseArray[$dateField] ?? '', [
                '1980-01-01T00:00:00.000Z',
                '1980-01-01 00:00:00',
                ])
            ) {
                $responseArray[$dateField] = null;
            } else {
                $responseArray[$dateField] = getDateOfUTC($responseArray[$dateField] ?? null);
            }
        }
    }
}
