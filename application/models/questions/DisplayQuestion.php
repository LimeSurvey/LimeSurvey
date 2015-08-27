<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/27/15
 * Time: 2:18 PM
 */

namespace ls\models\questions;


class DisplayQuestion extends \Question
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
        $result->setHtml(' ');
        return $result;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getColumns()
    {
        return [];
    }


}