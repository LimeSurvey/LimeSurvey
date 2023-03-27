<?php

namespace ls\tests;

/**
 * Tests for the LimeSurvey remote API.
 */
class RemoteControlQuestionPropertiesTest extends BaseTest
{
    /**
     * @var string
     */
    protected static $username = null;

    /**
     * @var string
     */
    protected static $password = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Import survey
        $filename = self::$surveysFolder . '/survey-dual-scale-question-api-test.lss';
        self::importSurvey($filename);
    }

    public function testGetDualQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'Q00'));
        $qid = $question->qid;

        $answerOptions = $this->getAnswerOptions($qid, 'en');

        $result = $this->handler->get_question_properties($sessionKey, $qid);
        echo PHP_EOL . 'The options are: ';
        var_dump($answerOptions);
        $this->assertSame($answerOptions['answeroptions'], $result['answeroptions'], 'The options were not returned correctly.');
    }

    private function getAnswerOptions($iQuestionID, $sLanguage)
    {
        $oAttributes = \Answer::model()->with('answerl10ns')
        ->findAll(
            't.qid = :qid and answerl10ns.language = :language',
            array(':qid' => $iQuestionID, ':language' => $sLanguage),
            array('order' => 'sortorder')
        );
        if (count($oAttributes) > 0) {
            $aData = array();
            foreach ($oAttributes as $oAttribute) {
                $aData[$oAttribute['code']][] = array(
                    'answer' => array_key_exists($sLanguage, $oAttribute->answerl10ns) ? $oAttribute->answerl10ns[$sLanguage]->answer : '',
                    'assessment_value' => $oAttribute['assessment_value'],
                    'scale_id' => $oAttribute['scale_id'],
                    'order' => $oAttribute['sortorder']
                );
            }
            $aResult['answeroptions'] = $aData;
        } else {
            $aResult['answeroptions'] = 'No available answer options';
        }

        return $aResult;
    }
}
