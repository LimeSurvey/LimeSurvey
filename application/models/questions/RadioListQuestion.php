<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/27/15
 * Time: 4:14 PM
 */

namespace ls\models\questions;


class RadioListQuestion extends SingleChoiceQuestion
{
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


        $html = \CHtml::radioButtonList($this->sgqa, $response->{$this->sgqa},
            \TbHtml::listData($this->getAnswers(), 'code', 'answer'));

        $result->setHtml($html);

        return $result;
    }
}