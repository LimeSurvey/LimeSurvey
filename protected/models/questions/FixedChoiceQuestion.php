<?php
namespace ls\models\questions;

use ls\interfaces\iAnswer;

/**
 * Base class for single choice questions without custom answers:
 * (Yes/No, 5 point scale .. )
 *
 * Class FixedChoiceQuestion
 * @package ls\models\questions
 */
abstract class FixedChoiceQuestion extends \ls\models\Question
{
    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 1;
    }

    /**
     * @return array Keys: column name, values: column type.
     * @throws Exception
     */
    public function getColumns()
    {
        $result = [$this->sgqa => "string(1)"];
        return $result;
    }

    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\iResponse $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);
        $html = \TbHtml::radioButtonList($this->sgqa, $response->{$this->sgqa}, \TbHtml::listData(
            $this->getAnswers(),
            function (iAnswer $answer) {
                return $answer->getCode();
            },
            function (iAnswer $answer) {
                return $answer->getLabel();
            }
        ), [
            'data-validation-expression' => $this->getExpressionManager($response)->getJavascript(implode(' and ', array_keys($this->getValidationExpressions())))
        ]);
        $result->setHtml($html);
        return $result;
    }

    /**
     * Does this question support custom answers?
     * @return boolean
     */
    public function getHasCustomAnswers()
    {
        return false;
    }


}