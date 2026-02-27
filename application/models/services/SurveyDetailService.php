<?php

namespace LimeSurvey\Models\Services;

use Survey;

class SurveyDetailService
{
    private const CACHE_KEY_PREFIX = 'survey_detail_';
    private const CACHE_DURATION = 3600; // Cache duration in seconds (1 hour)

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

        $cache = App()->cache->get(self::CACHE_KEY_PREFIX . $surveyId);
        // If cache is not found or contains invalid data
        if (empty($cache) || !is_string($cache)) {
            return false;
        }

        return json_decode($cache, true);
    }

    /**
     * Saves the cache
     * @param int $surveyId
     * @param array $content
     * @return void
     */
    public function saveCache(int $surveyId, array $content)
    {
        App()->cache->set(self::CACHE_KEY_PREFIX . $surveyId, json_encode($content), self::CACHE_DURATION);
    }

    /**
     * Removes the cache for the survey if it exists
     * @param int $surveyId
     * @return void
     */
    public function removeCache(int $surveyId)
    {
        App()->cache->delete(self::CACHE_KEY_PREFIX . $surveyId);
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
                return Survey::model()->findByPk(\QuestionGroup::model()->findByAttributes(['gid' => $entityMap[$key]])->sid);
            }
        }
        return null;
    }
}
