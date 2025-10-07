<?php

namespace LimeSurvey\Models\Services;

use Survey;

class SurveyDetailService
{
    /**
     * Gets the cache path of a survey
     * @param int $surveyId
     * @param bool $root
     * @return string
     */
    protected function getCachePath(int $surveyId, bool $root = false)
    {
        return \Yii::app()->getConfig('uploaddir') . "/surveys/" . $surveyId . ($root ? '' : '/survey-detail.html');
    }

    /**
     * Gets the survey cache if exists and false otherwise
     * @param int $surveyId
     * @param bool $clearCache
     * @return bool|string
     */
    public function getCache(int $surveyId, bool $clearCache = false)
    {
        if ($clearCache) {
            $this->removeCache($surveyId);
        }
        if (!file_exists($this->getCachePath($surveyId))) {
            return false;
        }
        if (time() - filemtime($this->getCachePath($surveyId)) > 3600) {
            $this->removeCache($surveyId);
            return false;
        }
        return json_decode(file_get_contents($this->getCachePath($surveyId)), true);
    }

    /**
     * Saves the cache
     * @param int $surveyId
     * @param array $content
     * @return void
     */
    public function saveCache(int $surveyId, array $content)
    {
        if (!is_dir($this->getCachePath($surveyId, true))) {
            mkdir($this->getCachePath($surveyId, true));
        }
        file_put_contents($this->getCachePath($surveyId), json_encode($content));
    }

    /**
     * Removes the cache for the survey if it exists
     * @param int $surveyId
     * @return void
     */
    public function removeCache(int $surveyId)
    {
        if (file_exists($this->getCachePath($surveyId))) {
            unlink($this->getCachePath($surveyId));
        }
    }

    /**
     * Gets survey object from entity map if possible
     * @param array $entityMap
     * @return Survey|null
     */
    public function getSurveyFromEntityMap($entityMap)
    {
        $supportedKeys = [
            'sid' => ['survey', 'accessMode', 'surveyStatus', 'question', 'subquestion', 'questionGroup', 'questionGroupReorder'],
            'qid_sid' => ['questionAttribute', 'questionCondition', 'questionL10n'],
            'gid_sid' => ['questionGroupL10n']
        ];
        foreach ($supportedKeys['sid'] as $key) {
            if (isset($entityMap[$key])) {
                return Survey::model()->findByPk($entityMap[$key]);
            }
        }
        foreach ($supportedKeys['qid_sid'] as $key) {
            if (isset($entityMap[$key])) {
                return Survey::model()->findByPk(\Question::model()->findByAttributes(['qid' => $entityMap[$key]])->sid);
            }
        }
        foreach ($supportedKeys['gid_sid'] as $key) {
            if (isset($entityMap[$key])) {
                if (isset($entityMap[$key])) {
                    return Survey::model()->findByPk(\QuestionGroup::model()->findByAttributes(['gid' => $entityMap[$key]])->sid);
                }
            }
        }
        return null;
    }
}
