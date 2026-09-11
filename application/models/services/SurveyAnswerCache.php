<?php

namespace LimeSurvey\Models\Services;

use Answer;

class SurveyAnswerCache
{
    /** @var array [qid][scaleId][code] => text */
    private $labels = [];

    /** @var array [qid][scaleId][code] => aid */
    private $aids = [];

    /** @var bool */
    private $loaded = false;

    /**
     * Load answer data for a survey into the cache.
     *
     * @param int $surveyId
     * @param string $language
     */
    public function load($surveyId, $language)
    {
        $this->labels = [];
        $this->aids = [];

        $answers = Answer::model()->with('answerl10ns', 'question')->findAll([
            'condition' => 'question.sid = :sid AND '
                . \Yii::app()->db->quoteTableName('answerl10ns')
                . '.language = :lang',
            'params' => [':sid' => $surveyId, ':lang' => $language],
            'order' => 'question.question_order, t.scale_id',
        ]);

        foreach ($answers as $answer) {
            $qid = $answer->question->qid;
            $this->aids[$qid][$answer->scale_id][$answer->code] = $answer->aid;
            if (isset($answer->answerl10ns[$language])) {
                $this->labels[$qid][$answer->scale_id][$answer->code]
                    = $answer->answerl10ns[$language]->answer;
            }
        }

        $this->loaded = true;
    }

    /**
     * Get the translated label for an answer.
     *
     * @param int|string|null $qid
     * @param int $scaleId
     * @param mixed $code
     * @return string|null
     */
    public function getLabel($qid, $scaleId, $code)
    {
        if ($qid === null || $code === null || $code === '') {
            return null;
        }
        return $this->labels[$qid][$scaleId][$code] ?? null;
    }

    /**
     * Get the answer ID for an answer.
     *
     * @param int|string|null $qid
     * @param int $scaleId
     * @param mixed $code
     * @return int|null
     */
    public function getAid($qid, $scaleId, $code)
    {
        if ($qid === null || $code === null || $code === '') {
            return null;
        }
        return $this->aids[$qid][$scaleId][$code] ?? null;
    }
}
