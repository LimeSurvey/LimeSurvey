<?php
namespace LimeSurvey\Models\Services;

use CHttpRequest;
use CWebUser;
use LimeExpressionManager;
use LSYii_Application;
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
    private CWebUser $modelUser;
    private CHttpRequest $modelRequest;

    /**
     *
     */
    public function __construct(
        Permission $modelPermission,
        Survey $modelSurvey,
        CWebUser $modelUser,
        CHttpRequest $modelRequest
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelSurvey = $modelSurvey;
        $this->modelUser = $modelUser;
        $this->modelRequest = $modelRequest;
    }

    /**
     * Updates a question group and all the languages.
     *
     * @param int $surveyId the survey id
     * @param int $questionGroupId the question group id
     * @param array $input has the data for a question group, including an array for languages
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
    public function updateGroup($surveyId, $questionGroupId, $input)
    {
        $survey = $this->getSurvey($surveyId);

        if (!$this->modelPermission->hasSurveyPermission($survey->sid, 'surveycontent', 'update')) {
            throw new Exception(
                'Permission denied'
            );
        }

        $questionGroup = QuestionGroup::model()->findByPk($questionGroupId);
        if (!$questionGroup) {
            throw new Exception(
                'Group not found'
            );
        }

        $questionGroup = $this->updateQuestionGroup($questionGroup, $input['questionGroup']);
        $this->updateQuestionGroupLanguages($questionGroup, $input['questionGroupI10N']);

        return $questionGroup;
    }

    /**
     * Creates a question group and all the languages.
     *
     * @param int $surveyId the survey id
     * @param array $input has the data for a question group, including an array for languages
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
        $this->updateQuestionGroupLanguages($questionGroup, $input['questionGroupI10N']);

        return $questionGroup;
    }

    /**
     * Deletes a question group and all its dependencies.
     *
     * @param int $questionGroupId the question group id
     * @param int $surveyId the survey id
     * @return int number of deleted rows
     * @throws CHttpException
     */
    public function deleteGroup($questionGroupId, $surveyId)
    {
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete questions."));
        }

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyId);

        $deletedGroups = QuestionGroup::deleteWithDependency($questionGroupId, $surveyId);
        if ($deletedGroups > 0) {
            QuestionGroup::model()->updateGroupOrder($surveyId);
        }
        LimeExpressionManager::UpgradeConditionsToRelevance($surveyId);

        return $deletedGroups;
    }

    /**
     * Returns a QuestionGroup (existing one or new created one)
     *
     * @param int $surveyId
     * @param int | null $iQuestionGroupId ID of group
     *
     * @return QuestionGroup
     */
    public function getQuestionGroupObject($surveyId, $questionGroupId = null)
    {
        $oQuestionGroup = QuestionGroup::model()->findByPk($questionGroupId);
        if (is_int($questionGroupId) && $oQuestionGroup === null) {
            throw new CHttpException(403, gT("Invalid ID"));
        } elseif ($oQuestionGroup == null) {
            $oQuestionGroup = new QuestionGroup();
            $oQuestionGroup->sid = $surveyId;
        }

        return $oQuestionGroup;
    }

    /**
     * Returns question group data for dataprovider of gridview in "Overview question and groups".
     * pageSize and search input parameters are taken into account.
     * @param int $surveyId
     * @param Survey | null $survey
     * @return QuestionGroup
     * @throws Exception
     */
    public function getGroupData($surveyId, $survey = null)
    {
        $survey = $survey === null ? $this->getSurvey($surveyId) : $survey;

        $questionGroup = new QuestionGroup('search');

        $questionGroupArray = $this->modelRequest->getParam('QuestionGroup', []);
        if (array_key_exists('group_name', $questionGroupArray)) {
            $questionGroup->group_name = $questionGroupArray['group_name'];
        }

        $pageSize = $this->modelRequest->getParam('pageSize', false);
        if ($pageSize) {
            $this->modelUser->setState('pageSize', (int)$pageSize);
        }
        $questionGroup->sid = $survey->primaryKey;
        $questionGroup->language = $survey->language;

        return $questionGroup;
    }

    /**
     * imports an uploaded question group. Returns array of import results.
     * @param int $surveyId
     * @param string $tmpDir
     * @return array
     */
    public function importQuestionGroup(int $surveyId, string $tmpDir)
    {
        $importResults = [];
        $sFullFilepath = $tmpDir . DIRECTORY_SEPARATOR . randomChars(20);
        $aPathInfo = pathinfo((string)$_FILES['the_file']['name']);
        $sExtension = $aPathInfo['extension'];

        if (strtolower($sExtension) !== 'lsg') {
            $fatalerror = gT("Unknown file extension");
        } elseif ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            $fatalerror = sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024);
        } elseif (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
            $fatalerror = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
        }

        if (isset($fatalerror)) {
            $importResults['fatalerror'] = $fatalerror;
        } else {
            try {
                App()->loadHelper('admin/import'); // I don't get App() to be injected without an Exception
                $importResults = XMLImportGroup(
                    $sFullFilepath,
                    $surveyId,
                    $this->modelRequest->getPost('translinksfields') == '1'
                );
            } catch (Exception $e) {
                $importResults['fatalerror'] = print_r($e->getMessage(), true);
            }

            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($surveyId);
        }
        $importResults['extension'] = $sExtension;
        unlink($sFullFilepath);

        return $importResults;
    }

    /**
     * Stores questiongroup languages.
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $dataSet array with languages
     * @return bool true if ALL languages could be safed, false otherwise
     */
    private function updateQuestionGroupLanguages($oQuestionGroup, $dataSet)
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
     * Reorders question groups.
     * Returns array containing success (boolean), message (string) and the unchanged grouparray from POST request.
     * @param int $surveyId
     * @return array
     * @throws Exception
     */
    public function reorderQuestionGroups(int $surveyId)
    {
        $survey = $this->getSurvey($surveyId);
        $groupArray = [];
        $success = true;
        $message = '';
        if (!$survey->isActive) {
            $groupArray = $this->modelRequest->getPost('grouparray', []);
            if (!empty($groupArray)) {
                foreach ($groupArray as $aQuestionGroup) {
                    //first set up the ordering for questiongroups
                    $oQuestionGroups = QuestionGroup::model()->findAll(
                        "gid=:gid AND sid=:sid",
                        [':gid' => $aQuestionGroup['gid'], ':sid' => $surveyId]
                    );
                    array_map(
                        function ($oQuestionGroup) use ($aQuestionGroup, &$success) {
                            $oQuestionGroup->group_order = $aQuestionGroup['group_order'];
                            $success = $success && $oQuestionGroup->save();
                        },
                        $oQuestionGroups
                    );

                    $aQuestionGroup['questions'] = $aQuestionGroup['questions'] ?? [];

                    foreach ($aQuestionGroup['questions'] as $aQuestion) {
                        $aQuestions = \Question::model()->findAll(
                            "qid=:qid AND sid=:sid",
                            [':qid' => $aQuestion['qid'], ':sid' => $surveyId]
                        );
                        array_walk(
                            $aQuestions,
                            function ($oQuestion) use ($aQuestion, &$success) {
                                $oQuestion->question_order = $aQuestion['question_order'];
                                $oQuestion->gid = $aQuestion['gid'];
                                if (safecount($oQuestion->subquestions) > 0) {
                                    $aSubquestions = $oQuestion->subquestions;
                                    array_walk(
                                        $aSubquestions,
                                        function ($oSubQuestion) use ($aQuestion, &$success) {
                                            $oSubQuestion->gid = $aQuestion['gid'];
                                            $success = $success && $oSubQuestion->save(true);
                                        }
                                    );
                                }
                                $success = $success && $oQuestion->save(true);
                            }
                        );
                    }
                }
            }
            QuestionGroup::model()->cleanOrder($surveyId);
        } else {
            $message = gT("You can't reorder in an active survey");
        }
        return [
            'success' => $success, 'message' => $message, 'grouparray' => $groupArray
        ];
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
    private function updateQuestionGroup($oQuestionGroup, $aQuestionGroupData)
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
