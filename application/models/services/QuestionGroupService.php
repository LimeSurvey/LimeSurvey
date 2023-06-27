<?php

namespace LimeSurvey\Models\Services;

use Survey;
use Permission;
use QuestionGroup;
use QuestionGroupL10n;

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
     *                     ['questionGroupl10N']
     *                         [en]
     *                            [group_name]
     *                            [description]
     *                         [...]    //more languages
     * @return void
     */
    public function updateGroup($surveyId, $questionGroupID, $input)
    {
        $survey = $this->modelSurvey->findByPk(
            $surveyId
        );

        if (!$survey) {
            throw new Exception(
                'Survey does not exist',
            );
        }

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

        $this->editQuestionGroup($questionGroup, $input['questionGroup']);
        $this->editQuestionGroupLanguages($questionGroup, $input);

        return $questionGroup;
    }

    /**
     * Stores questiongroup languages.
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $dataSet array with languages
     * @return bool true if ALL languages could be safed, false otherwise
     */
    public function editQuestionGroupLanguages(&$oQuestionGroup, $dataSet)
    {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionGroupL10n::model()->findByAttributes(
                ['gid' => $oQuestionGroup->gid,'language' => $sLanguage]
            );
            $i10N->setAttributes([
                'group_name' => $aI10NBlock['group_name'],
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
    public function editQuestionGroup(&$oQuestionGroup, $aQuestionGroupData)
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

    public function newGroup($surveyId, $input){

    }

    public function getGroupData($group_id){

    }

}
