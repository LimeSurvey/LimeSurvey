<?php
namespace ls\models\questions;


class DisplayQuestion extends \ls\models\Question
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