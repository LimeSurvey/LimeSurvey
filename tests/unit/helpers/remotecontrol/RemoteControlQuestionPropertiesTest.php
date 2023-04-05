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
                'AO01' => array(
                    'code' => 'AO01',
                    'answer' => 'Option 1, scale 1',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '0'
                ),
                'AO02' => array(
                    'code' => 'AO02',
                    'answer' => 'Option 2, scale 1',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '1'
                ),
                'AO03' => array(
                    'code' => 'AO03',
                    'answer' => 'Option 3, scale 1',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '2'
                )
            ),
            1 => array(
                'AO01' => array(
                    'code' => 'AO01',
                    'answer' => 'Option 1, scale 2',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '3'
                ),
                'AO02' => array(
                    'code' => 'AO02',
                    'answer' => 'Option 2, scale 2',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '4'
                ),
                'AO03' => array(
                    'code' => 'AO03',
                    'answer' => 'Option 3, scale 2',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '5'
                )
            ),
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'G01Q02'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid);
        $this->assertSame($answerOptionsByScaleId, $result['answeroptions'], 'The options were not returned correctly.');
    }

    public function testGetDualQuestionPropertiesSpecificLanguage()
    {

        //Options by scale id.
        $answerOptionsByScaleId = array(
            0 => array(
                'AO01' => array(
                    'code' => 'AO01',
                    'answer' => 'Opción 1, escala 1',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '0'
                ),
                'AO02' => array(
                    'code' => 'AO02',
                    'answer' => 'Opción 2, escala 1',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '1'
                ),
                'AO03' => array(
                    'code' => 'AO03',
                    'answer' => 'Opción 3, escala 1',
                    'assessment_value' => '0',
                    'scale_id' => '0',
                    'order' => '2'
                )
            ),
            1 => array(
                'AO01' => array(
                    'code' => 'AO01',
                    'answer' => 'Opción 1, escala 2',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '3'
                ),
                'AO02' => array(
                    'code' => 'AO02',
                    'answer' => 'Opción 2, escala 2',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '4'
                ),
                'AO03' => array(
                    'code' => 'AO03',
                    'answer' => 'Opción 3, escala 2',
                    'assessment_value' => '0',
                    'scale_id' => '1',
                    'order' => '5'
                )
            ),
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'G01Q02'));
        $qid = $question->qid;

        //Get properties in Spanish
        $result = $this->handler->get_question_properties($sessionKey, $qid, null, 'es');

        $this->assertSame($answerOptionsByScaleId, $result['answeroptions'], 'The options were not returned correctly.');
    }
}
