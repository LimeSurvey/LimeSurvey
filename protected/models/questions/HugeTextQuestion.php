<?php
namespace ls\models\questions;


use ls\interfaces\iResponse;

class HugeTextQuestion extends LongTextQuestion
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param Response $response
     * @param \ls\components\SurveySession $session
     * @return string
     */
    public function render(iResponse $response, \ls\components\SurveySession $session)
    {
        if (!isset($this->text_input_width)) {
            $this->text_input_width = 70;
        }

        if (!isset($this->display_rows)) {
            $this->display_rows = 70;
        }
        return parent::render($response, $session);
    }

    /**
     * This function return the class by question type
     * @param string question type
     * @return string Question class to be added to the container
     */
    public function getClasses()
    {
        $result = parent::getClasses();
        $result[] = 'text-huge';
        return $result;

    }


}