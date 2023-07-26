<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Proxy\ProxyExpressionManager;
use LimeSurvey\Models\Services\Proxy\ProxyQuestionGroup;
use LSYii_Application;
use Survey;
use Permission;
use Question;
use QuestionGroup;
use QuestionGroupL10n;
use Exception;
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class QuestionGroupService
{
    private Permission $modelPermission;
    private Survey $modelSurvey;
    private Question $modelQuestion;
    private QuestionGroup $modelQuestionGroup;
    private QuestionGroupL10n $modelQuestionGroupL10n;
    private ProxyExpressionManager $proxyExpressionManager;
    private ProxyQuestionGroup $proxyQuestionGroup;
    private LSYii_Application $yiiApp;

    public function __construct(
        Permission $modelPermission,
        Survey $modelSurvey,
        Question $modelQuestion,
        QuestionGroup $modelQuestionGroup,
        QuestionGroupL10n $modelQuestionGroupL10n,
        ProxyExpressionManager $proxyExpressionManager,
        ProxyQuestionGroup $proxyQuestionGroup,
        LSYii_Application $yiiApp
    ) {
        $this->modelPermission = $modelPermission;
        $this->modelSurvey = $modelSurvey;
        $this->modelQuestion = $modelQuestion;
        $this->modelQuestionGroup = $modelQuestionGroup;
        $this->modelQuestionGroupL10n = $modelQuestionGroupL10n;
        $this->proxyExpressionManager = $proxyExpressionManager;
        $this->proxyQuestionGroup = $proxyQuestionGroup;
        $this->yiiApp = $yiiApp;
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
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function updateGroup(int $surveyId, int $questionGroupId, array $input)
    {
        $survey = $this->getSurvey($surveyId);

        if (!$this->modelPermission->hasSurveyPermission($survey->sid, 'surveycontent', 'update')) {
            throw new PermissionDeniedException(
                'Permission denied'
            );
        }

        $questionGroup = $this->modelQuestionGroup->findByPk($questionGroupId);
        if (!$questionGroup) {
            throw new NotFoundException(
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
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function createGroup(int $surveyId, array $input)
    {
        if (!$this->modelPermission->hasSurveyPermission($surveyId, 'surveycontent', 'update')) {
            throw new PermissionDeniedException(
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
     * @return int|null number of deleted rows
     * @throws PermissionDeniedException
     */
    public function deleteGroup(int $questionGroupId, int $surveyId)
    {
        if (!$this->modelPermission->hasSurveyPermission($surveyId, 'surveycontent', 'delete')) {
            throw new PermissionDeniedException(
                gT("You are not authorized to delete questions.")
            );
        }

        $this->proxyExpressionManager->revertUpgradeConditionsToRelevance($surveyId);

        $deletedGroups = $this->proxyQuestionGroup->deleteQuestionGroupWithDependency($questionGroupId, $surveyId);
        if ($deletedGroups > 0) {
            $this->modelQuestionGroup->updateGroupOrder($surveyId);
        }
        $this->proxyExpressionManager->upgradeConditionsToRelevance($surveyId);

        return $deletedGroups;
    }

    /**
     * Returns a QuestionGroup (existing one or new created one)
     *
     * @param int $surveyId
     * @param int | null $questionGroupId ID of group
     *
     * @return QuestionGroup
     * @throws NotFoundException
     */
    public function getQuestionGroupObject(int $surveyId, ?int $questionGroupId = null)
    {
        $oQuestionGroup = $this->modelQuestionGroup->findByPk($questionGroupId);
        if (is_int($questionGroupId) && $oQuestionGroup === null) {
            throw new NotFoundException(gT("Invalid ID"));
        } elseif ($oQuestionGroup == null) {
            $oQuestionGroup = $this->modelQuestionGroup;
            $oQuestionGroup->sid = $surveyId;
        }

        return $oQuestionGroup;
    }

    /**
     * Returns question group data for dataprovider of gridview in "Overview question and groups".
     * search input parameter is taken into account.
     * @param Survey $survey
     * @param array $questionGroupArray
     * @return QuestionGroup
     */
    public function getGroupData(Survey $survey, array $questionGroupArray)
    {
        $questionGroup = $this->modelQuestionGroup;
        $questionGroup->setScenario('search');
        if (array_key_exists('group_name', $questionGroupArray)) {
            $questionGroup->group_name = $questionGroupArray['group_name'];
        }
        $questionGroup->sid = $survey->primaryKey;
        $questionGroup->language = $survey->language;

        return $questionGroup;
    }

    /**
     * imports an uploaded question group. Returns array of import results.
     * @param int $surveyId
     * @param string $tmpDir
     * @param string $transLinksFields
     * @return array
     */
    public function importQuestionGroup(int $surveyId, string $tmpDir, string $transLinksFields)
    {
        $importResults = [];
        $sFullFilepath = $tmpDir . DIRECTORY_SEPARATOR . randomChars(20);
        $aPathInfo = pathinfo((string)$_FILES['the_file']['name']);
        $sExtension = $aPathInfo['extension'];

        if (strtolower($sExtension) !== 'lsg') {
            $fatalerror = gT("Unknown file extension");
        } elseif ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            $fatalerror = sprintf(
                gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                getMaximumFileUploadSize() / 1024 / 1024
            );
        } elseif (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
            $fatalerror = gT(
                "An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."
            );
        }

        if (isset($fatalerror)) {
            $importResults['fatalerror'] = $fatalerror;
        } else {
            try {
                $this->yiiApp->loadHelper('admin/import');
                $importResults = XMLImportGroup(
                    $sFullFilepath,
                    $surveyId,
                    $transLinksFields == '1'
                );
            } catch (Exception $e) {
                $importResults['fatalerror'] = print_r($e->getMessage(), true);
            }
            $this->proxyExpressionManager->setDirtyFlag(); // so refreshes syntax highlighting
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
     * @return bool true if ALL languages could be saved, false otherwise
     */
    private function updateQuestionGroupLanguages(QuestionGroup $oQuestionGroup, array $dataSet)
    {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = $this->modelQuestionGroupL10n->findByAttributes(
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
     * Reorders question groups based on the group array. (Used in structure tab sidemenu tree drag&drop)
     * Returns array containing success (boolean), message (string).
     * @param int $surveyId
     * @param array $groupArray
     * @return array
     * @throws NotFoundException
     */
    public function reorderQuestionGroups(int $surveyId, array $groupArray)
    {
        $survey = $this->getSurvey($surveyId);
        $success = true;
        $message = '';
        if (!$survey->isActive) {
            if (!empty($groupArray)) {
                foreach ($groupArray as $aQuestionGroup) {
                    //first set up the ordering for questiongroups
                    $oQuestionGroups = $this->modelQuestionGroup->findAll(
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
                        $aQuestions = $this->modelQuestion->findAll(
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
            $this->modelQuestionGroup->cleanOrder($surveyId);
        } else {
            $message = gT("You can't reorder in an active survey");
        }
        return [
            'success' => $success,
            'message' => $message
        ];
    }

    /**
     * Reorder groups and questions
     * this is used by SurveyAdministrationController (Overview question &groups)
     *
     * @param int $iSurveyID Given Survey ID
     * @param array $orgdata Data to change
     *
     */
    public function reorderGroup($iSurveyID, $orgdata)
    {
        $result = array();
        $grouporder = 1;

        foreach ($orgdata as $ID => $parent) {
            if ($parent == 'root' && substr_compare($ID, 'g', 0, 1) === 0) {
                \QuestionGroup::model()->updateAll(
                    array('group_order' => $grouporder),
                    'gid=:gid',
                    array(':gid' => (int) substr($ID, 1))
                );
                $grouporder++;
            } elseif (substr_compare($ID, 'q', 0, 1) === 0) {
                $qid = (int) substr($ID, 1);
                $gid = (int) substr((string) $parent, 1);
                if (!isset($aQuestionOrder[$gid])) {
                    $aQuestionOrder[$gid] = 0;
                }

                $oQuestion = \Question::model()->findByPk($qid);
                /* @var integer old value of gid to check if updated */
                $oldGid = $oQuestion->gid;
                /* Update quuestion, and update other if saved */
                $oQuestion->gid = $gid;
                $oQuestion->question_order = $aQuestionOrder[$gid];
                if ($oQuestion->save(true)) {
                    if ($oldGid != $gid) {
                        fixMovedQuestionConditions($qid, $oldGid, $gid, $iSurveyID);
                    }
                    \Question::model()->updateAll(
                        array(
                            'question_order' => $aQuestionOrder[$gid],
                            'gid' => $gid
                        ),
                        'qid=:qid',
                        array(':qid' => $qid)
                    );
                    \Question::model()->updateAll(array('gid' => $gid), 'parent_qid=:parent_qid', array(':parent_qid' => $qid));
                    $aQuestionOrder[$gid]++;
                } else {
                    $result['type'] = 'error';
                    $result['question-titles'][] = $oQuestion->title;
                }
            }
        }
        $this->proxyExpressionManager->setDirtyFlag(); // so refreshes syntax highlighting

        if (!empty($result)) {
            return $result;
        }

        $result['type'] = 'success';
        return $result;
    }

    /**
     * Method to store and filter questionGroupData for editing a questionGroup
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $aQuestionGroupData
     *
     * @return QuestionGroup
     * @throws PersistErrorException
     */
    private function updateQuestionGroup(QuestionGroup $oQuestionGroup, array $aQuestionGroupData)
    {
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);
        if ($oQuestionGroup == null) {
            throw new PersistErrorException("Object update failed, input array malformed or invalid");
        }

        $saved = $oQuestionGroup->save();
        if (!$saved) {
            throw new PersistErrorException(
                "Saved question group failed, object may be inconsistent"
            );
        }
        return $oQuestionGroup;
    }

    /**
     * Method to store and filter questionData for a new question
     *
     * @param int $surveyId
     * @param array|null $aQuestionGroupData
     *
     * @return QuestionGroup
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    private function newQuestionGroup(int $surveyId, array $aQuestionGroupData = null)
    {
        $survey = $this->getSurvey($surveyId);
        $aQuestionGroupData = array_merge([
            'sid' => $survey->sid,
        ], $aQuestionGroupData);
        unset($aQuestionGroupData['gid']);

        $oQuestionGroup = $this->modelQuestionGroup;
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);

        if ($oQuestionGroup == null) {
            throw new PersistErrorException("Object creation failed, input array malformed or invalid");
        }
        // Always add at the end
        $oQuestionGroup->group_order = safecount($survey->groups) + 1;
        $saved = $oQuestionGroup->save();
        if (!$saved) {
            throw new PersistErrorException(
                "Object creation failed, couldn't save.\n ERRORS:"
                . print_r($oQuestionGroup->getErrors(), true)
            );
        }

        $i10N = [];
        foreach ($survey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = clone $this->modelQuestionGroupL10n;
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
     * @throws NotFoundException
     */
    private function getSurvey(int $surveyId)
    {
        $survey = $this->modelSurvey->findByPk($surveyId);
        if (!$survey) {
            throw new NotFoundException(
                'Survey does not exist',
            );
        }
        return $survey;
    }
}
