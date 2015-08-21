<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/21/15
 * Time: 10:13 AM
 */

namespace ls\models\questions;


use ls\interfaces\iRenderable;

class HugeTextQuestion extends LongTextQuestion implements iRenderable
{
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param Response $response
     * @param \SurveySession $session
     * @return string
     */
    public function render(\Response $response, \SurveySession $session)
    {
        if (!isset($this->text_input_width)) {
            $this->text_input_width = 70;
        }

        if (!isset($this->display_rows)) {
            $this->display_rows = 70;
        }
        return parent::render($response, $session);
    }


}