<?php

namespace LimeSurvey\Models\Services;

use LSYii_Application;
use Survey;
use Permission;
use Question;
use QuestionGroup;
use QuestionGroupL10n;
use Exception;
use LimeSurvey\Models\Services\{
    Proxy\ProxyExpressionManager,
    Proxy\ProxyQuestionGroup,
    Exception\PersistErrorException,
    Exception\NotFoundException,
    Exception\PermissionDeniedException
};

/**
 * @TODO There is a separate service GroupHelper.php whose function(s)
 *  should be moved here in the future
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
     * @param int $surveyId the survey ID
     * @param int $questionGroupId the question group id
     * @param array $input has the data for a question group, including an array for languages
     *      ['questionGroup']
     *          [gid]
     *          [sid]
     *          [group_order]
     *          [randomization_group]
     *          [grelevance]
     *      ['questionGroupL10N']
     *          [en]
     *              [group_name]
     *              [description]
     *          [...]    //more languages
     * @return QuestionGroup
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function updateGroup(int $surveyId, int $questionGroupId, array $input)
    {
        $questionGroup = $this->getQuestionGroupForUpdate(
            $surveyId,
            $questionGroupId
        );

        $questionGroup = $this->updateQuestionGroup(
            $questionGroup,
            $input['questionGroup']
        );
        $this->updateQuestionGroupLanguages(
            $questionGroup,
            $input['questionGroupI10N']
        );

        return $questionGroup;
    }

    /**
     * Checks permissions for updating, and returns a specific question group.
     * Throws an exception if no group can be found.
     * @param int $surveyId
     * @param int $questionGroupId
     * @return QuestionGroup
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function getQuestionGroupForUpdate(int $surveyId, int $questionGroupId)
    {
        $this->checkUpdatePermission($surveyId);
        $questionGroup = $this->modelQuestionGroup->findByPk($questionGroupId);
        if (!$questionGroup) {
            throw new NotFoundException(
                'Group not found'
            );
        }

        return $questionGroup;
    }

    /**
     * Creates a question group and all the languages.
     *
     * @param int $surveyId the survey ID
     * @param array $input has the data for a question group,
     *  including an array for languages
     *      ['questionGroup']
     *          [gid]
     *          [sid]
     *          [group_order]
     *          [randomization_group]
     *          [grelevance]
     *      ['questionGroupL10N']
     *          [en]
     *              [group_name]
     *              [description]
     *          [...]    //more languages
     * @return QuestionGroup
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function createGroup(int $surveyId, array $input)
    {
        $this->checkCreatePermission($surveyId);
        $questionGroup = $this->newQuestionGroup(
            $surveyId,
            $input['questionGroup'] ?? []
        );
        $this->updateQuestionGroupLanguages(
            $questionGroup,
            $input['questionGroupI10N'] ?? []
        );

        return $questionGroup;
    }

    /**
     * Deletes a question group and all its dependencies.
     *
     * @param int $questionGroupId the question group id
     * @param int $surveyId the survey ID
     * @return int|null number of deleted rows
     * @throws PermissionDeniedException
     */
    public function deleteGroup(int $questionGroupId, int $surveyId)
    {
        $this->checkDeletePermission($surveyId);
        $this->proxyExpressionManager->revertUpgradeConditionsToRelevance($surveyId);

        $deletedGroups = $this->proxyQuestionGroup->deleteQuestionGroupWithDependency(
            $questionGroupId,
            $surveyId
        );
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
     * @return QuestionGroup
     * @throws NotFoundException
     */
    public function getQuestionGroupObject(int $surveyId, ?int $questionGroupId = null)
    {
        $oQuestionGroup = $this->modelQuestionGroup->findByPk($questionGroupId);
        if (is_int($questionGroupId) && $oQuestionGroup === null) {
            throw new NotFoundException(gT('Invalid ID'));
        } elseif ($oQuestionGroup == null) {
            $oQuestionGroup = $this->modelQuestionGroup;
            $oQuestionGroup->sid = $surveyId;
        }

        return $oQuestionGroup;
    }

    /**
     * Returns question group data for dataprovider of gridview in
     * "Overview question and groups". Search input parameter is taken
     * into account.
     *
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
     * Imports an uploaded question group. Returns array of import results.
     *
     * @param int $surveyId
     * @param string $tmpDir
     * @param string $transLinksFields
     * @return array
     */
    public function importQuestionGroup(
        int $surveyId,
        string $tmpDir,
        string $transLinksFields
    ) {
        $importResults = [];
        $sFullFilepath = $tmpDir . DIRECTORY_SEPARATOR . randomChars(20);
        $aPathInfo = pathinfo((string)$_FILES['the_file']['name']);
        $sExtension = $aPathInfo['extension'];

        if (strtolower($sExtension) !== 'lsg') {
            $fatalerror = gT("Unknown file extension");
        } elseif (
            $_FILES['the_file']['error'] == 1
            || $_FILES['the_file']['error'] == 2
        ) {
            $fatalerror = sprintf(
                gT(
                    'Sorry, this file is too large. '
                    . 'Only files up to %01.2f MB are allowed.'
                ),
                getMaximumFileUploadSize() / 1024 / 1024
            );
        } elseif (
            !@move_uploaded_file(
                $_FILES['the_file']['tmp_name'],
                $sFullFilepath
            )
        ) {
            $fatalerror = gT(
                'An error occurred uploading your file. '
                . 'This may be caused by incorrect permissions '
                . 'for the application /tmp folder.'
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
            // so refreshes syntax highlighting
            $this->proxyExpressionManager->setDirtyFlag();
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
    public function updateQuestionGroupLanguages(
        QuestionGroup $oQuestionGroup,
        array $dataSet
    ) {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aL10NBlock) {
            $l10N = $this->modelQuestionGroupL10n->findByAttributes(
                ['gid' => $oQuestionGroup->gid, 'language' => $sLanguage]
            );
            if ($l10N) {
                if (isset($aL10NBlock['group_name'])) {
                    $l10N->setAttributes([
                        'group_name'  => $aL10NBlock['group_name']
                    ], false);
                }
                if (isset($aL10NBlock['description'])) {
                    $l10N->setAttributes([
                        'description' => $aL10NBlock['description']
                    ], false);
                }
                $storeValid = $storeValid && $l10N->save();
            }
        }

        return $storeValid;
    }

    /**
     * Reorders question groups based on the group array.
     * Returns array containing success (boolean), message (string).
     * @param int $surveyId
     * @param array $groupArray
     * @return array
     * @throws NotFoundException
     */
    public function reorderQuestionGroups(int $surveyId, array $groupArray)
    {
        $success = true;
        $message = '';
        if (!empty($groupArray)) {
            foreach ($groupArray as $aQuestionGroup) {
                //first set up the ordering for questiongroups
                $oQuestionGroups = $this->modelQuestionGroup->findAll(
                    "gid=:gid AND sid=:sid",
                    [':gid' => $aQuestionGroup['gid'], ':sid' => $surveyId]
                );
                array_map(
                    function ($oQuestionGroup) use (
                        $aQuestionGroup,
                        &$success
                    ) {
                        $oQuestionGroup->group_order = $aQuestionGroup['group_order'];
                        $success = $success && $oQuestionGroup->save();
                    },
                    $oQuestionGroups
                );

                $aQuestionGroup['questions'] = $aQuestionGroup['questions'] ?? [];

                foreach ($aQuestionGroup['questions'] as $aQuestion) {
                    $success = $this->updateQuestionsForReorder(
                        $aQuestion,
                        $surveyId,
                        $success
                    );
                }
            }
        } else {
            $message = gT("Nothing to reorder.");
        }
        $this->modelQuestionGroup->cleanOrder($surveyId);
        return [
            'success' => $success,
            'message' => $message
        ];
    }

    /**
     * Gets array with question related parameters (qid, question_order, gid)
     * and updates them in those questions and their subquestions
     * @param array $aQuestion
     * @param int $surveyId
     * @param bool $success
     * @return bool
     */
    private function updateQuestionsForReorder(
        array $aQuestion,
        int $surveyId,
        bool $success
    ) {
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
        return $success;
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
    public function updateQuestionGroup(
        QuestionGroup $oQuestionGroup,
        array $aQuestionGroupData
    ) {
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);
        if ($oQuestionGroup == null) {
            throw new PersistErrorException(
                "Object update failed, input array malformed or invalid"
            );
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
     * Creates a new question group, and also adds plain entries for the required
     * QuestionGroupL10n data
     *
     * @param int $surveyId
     * @param array|null $aQuestionGroupData
     *
     * @return QuestionGroup
     * @throws NotFoundException
     * @throws PersistErrorException
     */
    public function newQuestionGroup(int $surveyId, array $aQuestionGroupData = null)
    {
        $survey = $this->getSurvey($surveyId);
        $this->refreshModels();
        $aQuestionGroupData = array_merge([
            'sid' => $survey->sid,
        ], $aQuestionGroupData);
        unset($aQuestionGroupData['gid']);

        $oQuestionGroup = $this->modelQuestionGroup;
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);

        if ($oQuestionGroup == null) {
            throw new PersistErrorException(
                "Object creation failed, input array malformed or invalid"
            );
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

        $l10N = [];
        foreach ($survey->allLanguages as $sLanguage) {
            $l10N[$sLanguage] = clone $this->modelQuestionGroupL10n;
            $l10N[$sLanguage]->setAttributes([
                'gid'         => $oQuestionGroup->gid,
                'language'    => $sLanguage,
                'group_name'  => '',
                'description' => '',
            ], false);
            $l10N[$sLanguage]->save();
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

    /**
     * Resets questionGroup model for cases
     * when multiple groups are created simultaneously
     * @return void
     */
    private function refreshModels()
    {
        $this->modelQuestionGroup->unsetAttributes();
        $this->modelQuestionGroup->setIsNewRecord(true);
    }

    /**
     * @param int $surveyId
     * @return void
     * @throws PermissionDeniedException
     */
    public function checkUpdatePermission(int $surveyId)
    {
        if (
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'update'
            )
        ) {
            throw new PermissionDeniedException(
                'Permission denied'
            );
        }
    }

    /**
     * @param int $surveyId
     * @return void
     * @throws PermissionDeniedException
     */
    public function checkCreatePermission(int $surveyId)
    {
        if (
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'create'
            )
        ) {
            throw new PermissionDeniedException(
                'Permission denied'
            );
        }
    }

    /**
     * @param int $surveyId
     * @return void
     * @throws PermissionDeniedException
     */
    public function checkDeletePermission(int $surveyId)
    {
        $survey = $this->modelSurvey->findByPk($surveyId);
        if (
            $survey->isActive ||
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'delete'
            )
        ) {
            throw new PermissionDeniedException(
                gT('Access denied')
            );
        }
    }
}
