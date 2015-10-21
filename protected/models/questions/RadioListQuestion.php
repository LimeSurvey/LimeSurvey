<?php
namespace ls\models\questions;

class RadioListQuestion extends SingleChoiceQuestion
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param \ls\interfaces\Response $response
     * @param \ls\components\SurveySession $session
     * @return \ls\components\RenderedQuestion
     */
    public function render(\ls\interfaces\ResponseInterface $response, \ls\components\SurveySession $session)
    {
        $result = parent::render($response, $session);


        $html = \CHtml::radioButtonList($this->sgqa, $response->{$this->sgqa},
            \TbHtml::listData($this->getAnswers(), 'code', 'answer'));

        $result->setHtml($html);

        return $result;
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string ls\models\Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'list-radio';
        return $result;
    }


}