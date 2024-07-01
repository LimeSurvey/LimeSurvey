<?php

namespace LimeSurvey\Models\Services\Proxy;

use Survey;
use QuestionGroup;
use LimeExpressionManager;
use EmCacheHelper;

/**
 * Proxy Expression Manager Service
 *
 * Wraps expression manager functionality to make it injectable into services.
 *
 */
class ProxyExpressionManager
{
    private Survey $modelSurvey;
    private QuestionGroup $modelQuestionGroup;

    public function __construct(Survey $modelSurvey, QuestionGroup $modelQuestionGroup)
    {
        $this->modelSurvey = $modelSurvey;
        $this->modelQuestionGroup = $modelQuestionGroup;
    }

    /**
     * Reset Survey Expression Manager State
     *
     * This was originally located in the admin controller Database::resetEM().
     * The use of static methods make it impossible to inject LimeExpressionManager
     * as a dependency to enable testability. LimeExpressionManager needs to be
     * refactored to make it injectable as a dependency and to make its dependencies
     * injectable. This is a big task which I don't have time to tackle right now.
     * kfoster (2023-05-30)
     *
     * @param int $surveyId
     * @return void
     */
    public function reset($surveyId)
    {
        $survey = $this->modelSurvey->findByPk($surveyId);
        // UpgradeConditionsToRelevance SetDirtyFlag too
        LimeExpressionManager::SetDirtyFlag();
        LimeExpressionManager::UpgradeConditionsToRelevance(
            $surveyId
        );
        // Deactivate _UpdateValuesInDatabase
        LimeExpressionManager::SetPreviewMode('database');
        LimeExpressionManager::StartSurvey(
            $surveyId,
            'survey',
            $survey->attributes,
            true
        );
        LimeExpressionManager::StartProcessingPage(
            true,
            true
        );
        $aGrouplist = $this->modelQuestionGroup
            ->findAllByAttributes(['sid' => $surveyId]);
        foreach ($aGrouplist as $aGroup) {
            LimeExpressionManager::StartProcessingGroup(
                $aGroup['gid'],
                $survey->anonymized != 'Y',
                $surveyId
            );
            LimeExpressionManager::FinishProcessingGroup();
        }
        LimeExpressionManager::FinishProcessingPage();

        // Flush emcache when changes are made to the survey.
        EmCacheHelper::init(['sid' => $surveyId, 'active' => 'Y']);
        EmCacheHelper::flush();
    }

    /**
     * @see \LimeExpressionManager::RevertUpgradeConditionsToRelevance
     * @param int|null $surveyId
     * @param int|null $qid
     * @return void
     */
    public function revertUpgradeConditionsToRelevance(?int $surveyId = null, ?int $qid = null)
    {
        LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyId, $qid);
    }

    /**
     * @see \LimeExpressionManager::UpgradeConditionsToRelevance
     * @param int|null $surveyId
     * @param int|null $qid
     * @return void
     */
    public function upgradeConditionsToRelevance(?int $surveyId = null, ?int $qid = null)
    {
        LimeExpressionManager::UpgradeConditionsToRelevance($surveyId, $qid);
    }

    /**
     * @see \LimeExpressionManager::SetDirtyFlag
     * @return void
     */
    public function setDirtyFlag()
    {
        LimeExpressionManager::SetDirtyFlag();
    }
}
