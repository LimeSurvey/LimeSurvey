<?php
namespace ls\models\questions;

/**
 * Base class for array questions
 * Class ArrayQuestion
 * @package ls\models\questions
 * @property string $filterExpression
 */
abstract class BaseArrayQuestion extends \ls\models\Question
{
    public function getSubQuestionScales()
    {
        return 1;
    }


    public function relations()
    {
        return array_merge(parent::relations(), [
            'answers' => [self::HAS_MANY, \ls\models\Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
        ]);
    }

    public function getColumns()
    {
        $result = call_user_func_array('array_merge', array_map(function (\ls\models\Question $subQuestion) {
            $subResult = [];
            foreach ($subQuestion->columns as $name => $type) {
                $subResult[$this->sgqa . $name] = $type;
            }
            return $subResult;
        }, $this->subQuestions));
        return $result;
    }

    /**
     * Returns the fields for this question.
     * @return \ls\components\QuestionResponseField[]
     */
    public function getFields() {
        $result = [];
        $em = $this->getExpressionManager();
        foreach ($this->subQuestions as $subQuestion) {
            $result[] = $field = new \ls\components\QuestionResponseField($this->sgqa . $subQuestion->title, "{$this->title}_{$subQuestion->title}", $this);

            $filter = strtr($this->filterExpression, ['{VALUE}' => explode('|', $subQuestion->question, 2)[0]]);

            $script = empty($filter) ? $this->getRelevanceScript(false) : "{$this->getRelevanceScript(false)} && " . $em->getJavascript($filter);
            $field->setRelevanceScript($script);
        }
        return $result;


    }

    /**
     * This function gets an EM expression to use for array filters.
     */
    protected function getFilterExpression() {
        $relevance = [];
        if (!empty($this->array_filter)) {

            foreach (explode(',', $this->array_filter) as $code) {
                // We add dashes so strpos is never 0.
                $relevance[] = "strpos(list('---', that.$code.shown), '{VALUE}') != 0";
            }
        }

        if (!empty($this->array_filter_exclude)) {

            foreach (explode(',', $this->array_filter_exclude) as $code) {
                // We add dashes so strpos is never 0.
                $relevance[] = "strpos(list('---', that.$code.shown), '{VALUE}') == 0";
            }
        }


        return implode('&&', $relevance);
    }

    /**
     * @param bool|false $includeFields Whether to include the condition that at least one field must be relevant. (to prevent infinite recursion)
     * @return bool|string
     * @throws \Exception
     */
    public function getRelevanceScript($includeFields = true)
    {
        $result = parent::getRelevanceScript();
        // This question is irrelevant if all subquestions are irrelevant.
        if ($includeFields && (is_string($result) || $result === true)) {
            foreach($this->getFields() as $field) {
                $clauses[] = "EM.isRelevant('{$field->getCode()}')";
            }

            if (is_string($result)) {
                $result .= implode(" || ", $clauses);
            } else {
                $result = implode(" || ", $clauses);
            }
        }
        return $result;
    }

    /**
     * Does this question support custom subquestions?
     * @return boolean
     */
    public function getHasCustomSubQuestions()
    {
        return true;
    }


}