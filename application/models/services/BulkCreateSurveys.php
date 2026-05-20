<?php

namespace LimeSurvey\Models\Services;

use Survey;
use SurveyLanguageSetting;
use Permission;
use LimeSurvey\Datavalueobjects\SimpleSurveyValues;

/**
 * This service class creates a bulk of 1000 surveys.
 * Could be used for testing purposes or to quickly generate a large number of surveys for performance testing.
 *
 * Class BulkCreateSurveys
 * @package LimeSurvey\Models\Services
 */
class BulkCreateSurveys
{
    /** @var int number of surveys to create */
    const SURVEY_COUNT = 1000;

    /** @var int the user ID used for permissions */
    private $userId;

    /** @var string base language for all surveys */
    private $baseLanguage;

    /** @var int survey group ID */
    private $surveyGroupId;

    /**
     * BulkCreateSurveys constructor.
     *
     * @param int    $userId        The ID of the user creating the surveys
     * @param string $baseLanguage  Base language code (e.g. 'en')
     * @param int    $surveyGroupId Survey group ID the surveys should belong to
     */
    public function __construct($userId, $baseLanguage = 'en', $surveyGroupId = 1)
    {
        $this->userId = $userId;
        $this->baseLanguage = $baseLanguage;
        $this->surveyGroupId = $surveyGroupId;
    }

    /**
     * Creates 1000 surveys and returns an array with results.
     *
     * Each entry in the result array contains:
     *   - 'index'   => int   (1-based index of this survey)
     *   - 'success' => bool
     *   - 'sid'     => int|null  (survey ID on success, null on failure)
     *   - 'title'   => string
     *
     * @return array
     */
    public function run(): array
    {
        $results = [];
        $permissionModel = new Permission();

        for ($i = 1; $i <= self::SURVEY_COUNT; $i++) {
            $title = sprintf('Bulk Survey %d', $i);

            $simpleSurveyValues = new SimpleSurveyValues();
            $simpleSurveyValues->baseLanguage  = $this->baseLanguage;
            $simpleSurveyValues->title         = $title;
            $simpleSurveyValues->surveyGroupId = $this->surveyGroupId;

            $survey           = new Survey();
            $languageSettings = new SurveyLanguageSetting();

            $createSurveyService = new CreateSurvey($survey, $languageSettings);
            $createdSurvey       = $createSurveyService->createSimple(
                $simpleSurveyValues,
                $this->userId,
                $permissionModel
            );

            if ($createdSurvey instanceof Survey) {
                $results[] = [
                    'index'   => $i,
                    'success' => true,
                    'sid'     => $createdSurvey->sid,
                    'title'   => $title,
                ];
            } else {
                $results[] = [
                    'index'   => $i,
                    'success' => false,
                    'sid'     => null,
                    'title'   => $title,
                ];
            }
        }

        return $results;
    }

    /**
     * Returns the number of successful survey creations from the result array.
     *
     * @param array $results Return value of run()
     * @return int
     */
    public static function countSuccessful(array $results): int
    {
        return count(array_filter($results, static function ($entry) {
            return $entry['success'] === true;
        }));
    }
}

