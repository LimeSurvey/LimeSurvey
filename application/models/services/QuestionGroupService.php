<?php

namespace LimeSurvey\Models\Services;

use LimeExpressionManager;
use Survey;
use Permission;
use QuestionGroup;
use QuestionGroupL10n;
use Exception;
use CException;
use CHttpException;

class QuestionGroupService
{
    private Permission $modelPermission;
    private Survey $modelSurvey;

    /**
     *
     */
    public function __construct(
        Permission $modelPermission,
        Survey $modelSurvey
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelSurvey = $modelSurvey;
    }

    /**
     * Updates a question group and all the languages.
     *
     * @param $surveyId int the survey id
     * @param $questionGroupID int the question group id
     * @param $input  array   has the data for a question group, including an array for languages
     *                     ['questionGroup']
     *                          [gid]
     *                          [sid]
     *                          [group_order]
     *                          [randomization_group]
     *                          [grelevance]
     *                     ['questionGroupI10N']
     *                         [en]
     *                            [group_name]
     *                            [description]
     *                         [...]    //more languages
     * @return QuestionGroup
     */
    public function updateGroup($surveyId, $questionGroupID, $input)
    {
        $survey = $this->getSurvey($surveyId);

        if (!$this->modelPermission->hasSurveyPermission($survey->sid, 'surveycontent', 'update')) {
            throw new Exception(
                'Permission denied'
            );
        }

        $questionGroup = QuestionGroup::model()->findByPk($questionGroupID);
        if (!$questionGroup) {
            throw new Exception(
                'Group not found'
            );
        }

        $questionGroup = $this->editQuestionGroup($questionGroup, $input['questionGroup']);
        $this->editQuestionGroupLanguages($questionGroup, $input['questionGroupI10N']);

        return $questionGroup;
    }

    /**
     * Creates a question group and all the languages.
     *
     * @param $surveyId int the survey id
     * @param $input  array   has the data for a question group, including an array for languages
     *                     ['questionGroup']
     *                          [gid]
     *                          [sid]
     *                          [group_order]
     *                          [randomization_group]
     *                          [grelevance]
     *                     ['questionGroupI10N']
     *                         [en]
     *                            [group_name]
     *                            [description]
     *                         [...]    //more languages
     * @return QuestionGroup
     */
    public function createGroup($surveyId, $input)
    {
        if (!$this->modelPermission->hasSurveyPermission($surveyId, 'surveycontent', 'update')) {
            throw new Exception(
                'Permission denied'
            );
        }

        $questionGroup = $this->newQuestionGroup($surveyId, $input['questionGroup']);
        $this->editQuestionGroupLanguages($questionGroup, $input['questionGroupI10N']);

        return $questionGroup;
    }

    /**
     * Deletes a question group and all its dependencies.
     *
     * @param $questionGroupID int the question group id
     * @param $surveyId int the survey id
     * @return int number of deleted rows
     * @throws CHttpException
     */
    public function deleteGroup($questionGroupID, $surveyId)
    {
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete questions."));
        }

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyId);

        $deletedGroups = QuestionGroup::deleteWithDependency($questionGroupID, $surveyId);
        if($deletedGroups > 0) {
            QuestionGroup::model()->updateGroupOrder($surveyId);
        }
        LimeExpressionManager::UpgradeConditionsToRelevance($surveyId);

        return $deletedGroups;
    }

    /**
     * Stores questiongroup languages.
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $dataSet array with languages
     * @return bool true if ALL languages could be safed, false otherwise
     */
    public function editQuestionGroupLanguages($oQuestionGroup, $dataSet)
    {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionGroupL10n::model()->findByAttributes(
                ['gid' => $oQuestionGroup->gid, 'language' => $sLanguage]
            );
            $i10N->setAttributes([
                'group_name'  => $aI10NBlock['group_name'],
                'description' => $aI10NBlock['description'],
            ], false);
            $storeValid = $storeValid && $i10N->save();
        }

        return $storeValid;
    }

    /**
     * Method to store and filter questionGroupData for editing a questionGroup
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $aQuestionGroupData
     *
     * @return QuestionGroup
     *
     * @throws CException
     */
    public function editQuestionGroup($oQuestionGroup, $aQuestionGroupData)
    {
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);
        if ($oQuestionGroup == null) {
            throw new CException("Object update failed, input array malformed or invalid");
        }

        $saved = $oQuestionGroup->save();
        if (!$saved) {
            throw new CException(
                "Saved question group failed, object may be inconsistent"
            );
        }
        return $oQuestionGroup;
    }

    /**
     * Method to store and filter questionData for a new question
     *
     * @param int $surveyId
     * @param array $aQuestionGroupData
     *
     * @return QuestionGroup
     * @throws CException
     */
    private function newQuestionGroup($surveyId, $aQuestionGroupData = null)
    {
        $survey = $this->getSurvey($surveyId);
        $aQuestionGroupData = array_merge([
            'sid' => $survey->sid,
        ], $aQuestionGroupData);
        unset($aQuestionGroupData['gid']);

        $oQuestionGroup = new QuestionGroup();
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);

        if ($oQuestionGroup == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }
        // Always add at the end
        $oQuestionGroup->group_order = safecount($survey->groups) + 1;
        $saved = $oQuestionGroup->save();
        if ($saved == false) {
            throw new CException(
                "Object creation failed, couldn't save.\n ERRORS:"
                . print_r($oQuestionGroup->getErrors(), true)
            );
        }

        $i10N = [];
        foreach ($survey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionGroupL10n();
            $i10N[$sLanguage]->setAttributes([
                'gid'         => $oQuestionGroup->gid,
                'language'    => $sLanguage,
                'group_name'  => '',
                'description' => '',
            ], false);
            $i10N[$sLanguage]->save();
        }

        return $oQuestionGroup;
    }

    /**
     * @param int $surveyId
     * @return QuestionGroup
     */
    public function getGroupData($surveyId)
    {
        $survey = $this->getSurvey($surveyId);

        $questionGroup = new QuestionGroup('search');

        if (isset($_GET['QuestionGroup']['group_name'])) {
            $questionGroup->group_name = $_GET['QuestionGroup']['group_name'];
        }

        if (isset($_GET['pageSize'])) {
            App()->user->setState('pageSize', (int)$_GET['pageSize']);
        }
        $questionGroup->sid = $survey->primaryKey;
        $questionGroup->language = $survey->language;

        return $questionGroup;
    }

    /**
     * @param int $surveyId
     * @return Survey
     */
    private function getSurvey($surveyId)
    {
        $survey = $this->modelSurvey->findByPk($surveyId);
        if (!$survey) {
            throw new Exception(
                'Survey does not exist',
            );
        }
        return $survey;
    }
}
