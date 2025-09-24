<?php

namespace LimeSurvey\Models\Services;

use App;
use LSHttpRequest;
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
    /** @var ?int */
    private $newSurveyId;

    /** @var array */
    private $options;

    /**
     * @var Survey */
    private $sourceSurvey;

    /**
     * @param Survey $sourceSurvey
     * @param array $options
     * @param int $newSurveyId
     */
    public function __construct($sourceSurvey, $options, $newSurveyId)
    {
        $this->sourceSurvey = $sourceSurvey;
        $this->options = $options;
        $this->newSurveyId = $newSurveyId;
    }

    /**
     * Copy the survey and return the results.
     *
     * It first extracts the original survey data to xml and afterward imports the xml data
     * as a survey.
     * All the functions used here (surveyGetXMLData, XMLImportSurvey) are very old functions.
     *
     * @return array | null  Returns an array with success and error messages or null if source survey does not exist.
     * @throws \Exception
     */
    public function copy()
    {
        if (!$this->sourceSurvey) {
            return null;
        }

        //for other functions deeply hidden the naming is relevant...
        if (isset($this->options['excludeAnswers'])) {
            $this->options['answers'] = $this->options['excludeAnswers'];
        }

        if (isset($this->options['resetConditions'])) {
            $this->options['conditions'] = $this->options['resetConditions'];
        }

        App()->loadHelper('export');
        $copySurveyData = surveyGetXMLData($this->sourceSurvey->sid, $this->options);

        App()->loadHelper('admin/import');
        $aImportResults = XMLImportSurvey(
            '',
            $copySurveyData,
            $this->sourceSurvey->currentLanguageSettings->surveyls_title . '- Copy',
            $this->newSurveyId,
            $this->options['copyResources']
        );

        if (isset($this->options['resetConditions'])) {
            Question::model()->updateAll(array('relevance' => '1'), 'sid=' . $aImportResults['newsid']);
            QuestionGroup::model()->updateAll(array('grelevance' => '1'), 'sid=' . $aImportResults['newsid']);
        }

        if (isset($this->options['resetResponseId'])) {
            $oSurvey = Survey::model()->findByPk($aImportResults['newsid']);
            $oSurvey->autonumber_start = 0;
            $oSurvey->save();
        }

        if (!isset($this->options['excludePermissions'])) {
            Permission::model()->copySurveyPermissions($this->sourceSurvey->sid, $aImportResults['newsid']);
        }

        if (!empty($aImportResults['newsid']) && $this->options['copyResources']) {
            $resourceCopier = new CopySurveyResources();
            [, $errorFilesInfo] = $resourceCopier->copyResources($this->sourceSurvey->sid, $aImportResults['newsid']);
            if (!empty($errorFilesInfo)) {
                $aImportResults['importwarnings'][] = gT("Some resources could not be copied from the source survey");
            }
        }

        return $aImportResults;
    }
}
