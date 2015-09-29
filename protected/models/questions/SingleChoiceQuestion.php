<?php
namespace ls\models\questions;

/**
 * Class SingleChoiceQuestion
 * @package ls\models\questions
 */
class SingleChoiceQuestion extends ChoiceQuestion
{
    public function getAnswerScales()
    {
        return 1;
    }


    public function relations()
    {
        return array_merge(parent::relations(), [
            'answers' => [self::HAS_MANY, \ls\models\Answer::class, 'question_id', 'order' => 'sortorder', 'index' => 'code']
        ]);
    }

    /**
     * @return array Column definitions for SingleChoiceQuestion type(s)
     */
    public function getColumns()
    {
        $result = [$this->sgqa => "string(5)"];

        if ($this->other == 'Y') {
            $result[$this->sgqa . 'other'] = 'text';
        }
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
        $result->setHtml('TODO');
        return $result;
    }


}