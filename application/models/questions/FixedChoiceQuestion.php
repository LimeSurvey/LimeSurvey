<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 7/23/15
 * Time: 9:46 AM
 */

namespace ls\models\questions;

/**
 * Base class for single choice questions without custom answers:
 * (Yes/No, 5 point scale .. )
 *
 * Class FixedChoiceQuestion
 * @package ls\models\questions
 */
abstract class FixedChoiceQuestion extends \Question
{
    /**
     * Returns the number of scales for answers.
     * @return int Range: {0, 1, 2}
     */
    public function getAnswerScales()
    {
        return 0;
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
     * @param \SurveySession $session
     * @return \RenderedQuestion
     */
    public function render(\ls\interfaces\iResponse $response, \SurveySession $session)
    {
        $result = parent::render($response, $session);


        $html = \TbHtml::radioButtonList($this->sgqa, $response->{$this->sgqa}, $this->getAnswers());
        $result->setHtml($html);
        return $result;
    }


}