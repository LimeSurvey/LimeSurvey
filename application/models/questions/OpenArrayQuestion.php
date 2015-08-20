<?php
namespace ls\models\questions;
use Question;
/**
 * Class OpenArrayQuestion
 * Used by array question types that have free inputs, like:
 * Array (Texts) and Array (Numbers)
 * @package ls\models\questions
 */
class OpenArrayQuestion extends ArrayQuestion
{
    public function getSubQuestionScales()
    {
        return 2;
    }

    public function getColumns()
    {
        $result = [];
        $yScale = array_filter($this->subQuestions, function (Question $question) {
            return $question->scale_id == 0;
        });

        $xScale = array_filter($this->subQuestions, function (Question $question) {
            return $question->scale_id == 1;
        });

        foreach($yScale as $yQuestion) {
            foreach($xScale as $xQuestion) {
                /**
                 * @todo Change this to use integer for array numbers question type.
                 */
                $result["{$this->sgqa}{$yQuestion->title}_{$xQuestion->title}"] = 'text';
            }
        }
        return $result;
    }

    /**
     * Returns the fields for this question.
     * @return QuestionResponseField[]
     */
    public function getFields()
    {
        bP();
        if (empty($this->_fields)) {
            $yScale = array_filter($this->subQuestions, function (Question $question) {
                return $question->scale_id == 0;
            });

            $xScale = array_filter($this->subQuestions, function (Question $question) {
                return $question->scale_id == 1;
            });

            foreach($yScale as $yQuestion) {
                foreach($xScale as $xQuestion) {
                    $this->_fields[] = $field = new \QuestionResponseField("{$this->sgqa}{$yQuestion->title}_{$xQuestion->title}", $this);
                }
            }
        }
        eP();
        return $this->_fields;

    }


}