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
    /** @var LSHttpRequest */
    private $request;

    /** @var int */
    private $sourceSurveyId;

    /** @var string */
    private $newSurveyName;

    /** @var ?int */
    private $newSurveyId;

    /** @var array */
    private $options;

    /**
     * @param LSHttpRequest $request
     * @param string $newSurveyName
     * @param int|null $newSurveyId
     *
     */
    public function __construct($request, $newSurveyName, $newSurveyId)
    {
        $this->request = $request;
        $this->sourceSurveyId = null;
        $this->newSurveyName = $newSurveyName;
        $this->newSurveyId = $newSurveyId;
        $this->options = $this->initialiseOptions();
    }

    /**
     * Initialise the options array based on the user's decision or default settings.
     *
     * @return array
     */
    private function initialiseOptions(): array
    {
        $this->sourceSurveyId = sanitize_int($this->request->getPost('surveyIdToCopy'));

        if ($this->sourceSurveyId === null) {
            $this->sourceSurveyId = sanitize_int($this->request->getParam('surveyIdToCopy'));
        }

        $options = [];
        $options['copyResources'] = true;
        $options['excludeQuotas'] = true;
        $options['excludePermissions'] = true;
        $options['excludeAnswers'] = true;
        $options['resetConditions'] = true;
        $options['resetStartEndDate'] = true;
        $options['resetResponseId'] = true;

        //Survey resource files and adapt links
        $option = $this->request->getPost('copysurveytranslinksfields');
        if (isset($option)) { //user decision
            $options['copyResources'] = $option == "1";
        }

        $option = $this->request->getPost('copysurveyexcludequotas');
        if (isset($option)) { //user decision
            $options['excludeQuotas'] = $option == "1";
        }

        $option = $this->request->getPost('copysurveyexcludepermissions');
        if (isset($option)) { //user decision
            $options['excludePermissions'] = $option == "1";
        }

        $option = $this->request->getPost('copysurveyexcludeanswers');
        if (isset($option)) { //user decision
            $options['excludeAnswers'] = $option == "1";
        }

        $option = $this->request->getPost('copysurveyresetconditions');
        if (isset($option)) { //user decision
            $options['resetConditions'] = $option == "1";
        }

        $option = $this->request->getPost('copysurveyresetstartenddate');
        if (isset($option)) { //user decision
            $options['resetStartEndDate'] = $option == "1";
        }

        $option = $this->request->getPost('copysurveyresetresponsestartid');
        if (isset($option)) { //user decision
            $options['resetResponseId'] = $option == "1";
        }

        return $options;
    }

    /**
     * Copy the survey and return the results.
     *
     * It first extracts the original survey data to xml and afterward imports the xml data
     * as a survey.
     * All the functions used here (surveyGetXMLData, XMLImportSurvey) are very old functions.
     *
     * @return array
     * @throws \Exception
     */
    public function copy(): array
    {
        //for other functions deeply hidden the naming is relevant...
        $this->options['answers'] = $this->options['excludeAnswers'];
        $this->options['conditions'] = $this->options['resetConditions'];

        App()->loadHelper('export');
        $copySurveyData = surveyGetXMLData($this->sourceSurveyId, $this->options);

        App()->loadHelper('admin/import');
        $aImportResults = XMLImportSurvey(
            '',
            $copySurveyData,
            $this->newSurveyName,
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
            Permission::model()->copySurveyPermissions($this->sourceSurveyId, $aImportResults['newsid']);
        }

        if (!empty($aImportResults['newsid']) && $this->options['copyResources']) {
            $resourceCopier = new CopySurveyResources();
            [, $errorFilesInfo] = $resourceCopier->copyResources($this->sourceSurveyId, $aImportResults['newsid']);
            if (!empty($errorFilesInfo)) {
                $aImportResults['importwarnings'][] = gT("Some resources could not be copied from the source survey");
            }
        }

        return $aImportResults;
    }
}
