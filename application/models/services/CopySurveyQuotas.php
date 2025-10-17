<?php

namespace LimeSurvey\Models\Services;

use Quota;
use QuotaLanguageSetting;
use QuotaMember;
use Survey;

class CopySurveyQuotas
{
    /**
     * @var Survey The source survey.
     */
    private Survey $sourceSurvey;

    /**
     * @var Survey The destination survey.
     */
    private Survey $destinationSurvey;

    public function __construct(Survey $sourceSurvey, Survey $destinationSurvey)
    {
        $this->sourceSurvey = $sourceSurvey;
        $this->destinationSurvey = $destinationSurvey;
    }

    /**
     * Copy all quotas for the source survey to the destination survey.
     *
     * @param array $mappingQuestionIds A mapping of question IDs between the source and destination surveys.
     *              $mappingQuestionIds[sourceQuestionId] = destinationQuestionId;
     *              (e.g. $mappingQuestionIds[3520] = 7452;)
     *
     * @return int amount of copied quotas
     */
    public function copyQuotas($mappingQuestionIds)
    {
        // Get all quotas from the source survey
        $surveyQuotas = Quota::model()->findAllByAttributes(['sid' => $this->sourceSurvey->sid]);

        $cnt = 0;
        foreach ($surveyQuotas as $quota) {
            /** @var Quota $quota */
            $newQuota = new Quota();
            $newQuota->attributes = $quota->attributes;
            $newQuota->sid = $this->destinationSurvey->sid;
            if ($newQuota->save()) {
                $cnt++;
                //copy quota languages
                $surveyQuotasLanguages = QuotaLanguageSetting::model()->findAllByAttributes(['quotals_quota_id' => $quota->id]);
                if (!empty($surveyQuotasLanguages)) {
                    foreach ($surveyQuotasLanguages as $quotaLanguage) {
                        $newQuotaLanguage = new QuotaLanguageSetting();
                        $newQuotaLanguage->attributes = $quotaLanguage->attributes;
                        $newQuotaLanguage->quotals_quota_id = $newQuota->id;
                        $newQuotaLanguage->save();
                    }
                }
                //copy quota members (relation between quota and question)
                $quotaMembers = QuotaMember::model()->findAllByAttributes([
                    'sid' => $this->sourceSurvey->sid,
                    'quota_id' => $quota->id
                ]);
                foreach ($quotaMembers as $quotaMember) {
                    $newQuotaMember = new QuotaMember();
                    $newQuotaMember->attributes = $quotaMember->attributes;
                    $newQuotaMember->quota_id = $newQuota->id;
                    $newQuotaMember->sid = $this->destinationSurvey->sid;
                    // map source question id to destination question id
                    $newQuotaMember->qid = $mappingQuestionIds[$quotaMember->qid];
                    $newQuotaMember->save();
                }
            }
        }
        return $cnt;
    }
}
