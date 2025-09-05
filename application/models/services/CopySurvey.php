<?php

namespace LimeSurvey\Models\Services;

use App;
use Question;
use QuestionGroup;
use Survey;
use Permission;

/**
 * This class is responsible for copying a survey.
 *
 * Class CopySurvey
 * @package LimeSurvey\Models\Services
 */
class CopySurvey
{
    /** @var int */
    private $sourceSurveyId;

    /** @var string */
    private $newSurveyName;

    /** @var ?int */
    private $newSurveyId;

    /** @var array */
    private $options;

    /**
     * @param int $sourceSurveyId
     * @param string $newSurveyName
     * @param int|null $newSurveyId
     * @param array $options
     *              Possible options:
     *                  ['excludeQuotas'] --> Exclude quota settings from the copied survey
     *
     */
    public function __construct($sourceSurveyId, $newSurveyName, $newSurveyId, $options)
    {
        $this->sourceSurveyId = $sourceSurveyId;
        $this->newSurveyName = $newSurveyName;
        $this->newSurveyId = $newSurveyId;
        $this->options = $options;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function copy(): array
    {
        App()->loadHelper('export');
        $copySurveyData = surveyGetXMLData($this->sourceSurveyId, $this->options['excludes']);

        App()->loadHelper('admin/import');
        $aImportResults = XMLImportSurvey(
            '',
            $copySurveyData,
            $this->newSurveyName,
            $this->newSurveyId,
            $this->options['translateLinks']
        );



        return $aImportResults;
    }
}
