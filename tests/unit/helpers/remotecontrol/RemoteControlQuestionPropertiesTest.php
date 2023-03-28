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

        //Options by scale id.
        $answerOptionsByScaleId = array(
            0 => array(
                1 => array(
                    'code' => '1',
                    'answer' => 'Good',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '0'
                ),
                2 => array(
                    'code' => '2',
                    'answer' => 'Important',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '1'
                )
            ),
            1 => array(
                1 => array(
                    'code' => '1',
                    'answer' => 'Bad',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '2'
                ),
                2 => array(
                    'code' => '2',
                    'answer' => 'Not important',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '3'
                )
            ),
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'Q00'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid);
        echo PHP_EOL . 'The options are: ';
        var_dump($result['answeroptions']);
        $this->assertSame($answerOptionsByScaleId, $result['answeroptions_multiscale'], 'The options were not returned correctly.');
    }
}
