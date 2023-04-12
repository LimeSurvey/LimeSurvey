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

        //Checking options
        $this->assertSame($answerOptionsByScaleId, $result['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('1', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertSame('2', $result['question_order'], 'The question order is not correct.');
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

        //Checking options
        $this->assertSame($answerOptionsByScaleId, $result['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('1', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertSame('2', $result['question_order'], 'The question order is not correct.');
    }

    public function testGetArrayByColumnQuestionProperties()
    {
        $answerOptions = array(
            'AO01' => array(
                'answer' => 'Option 1',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '0'
            ),
            'AO02' => array(
                'answer' => 'Option 2',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '1'
            ),
            'AO03' => array(
                'answer' => 'Option 3',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '2'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'G01Q03'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertSame($answerOptions, $result['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('H', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertSame('3', $result['question_order'], 'The question order is not correct.');
    }

    public function testGetArrayQuestionProperties()
    {
        $answerOptions = array(
            'AO01' => array(
                'answer' => 'Option 1',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '0'
            ),
            'AO02' => array(
                'answer' => 'Option 2',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '1'
            ),
            'AO03' => array(
                'answer' => 'Option 3',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '2'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'G01Q04'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertSame($answerOptions, $result['answeroptions'], 'The options were not returned correctly.');

        //Checking subquestions
        $subquestions = array(
            'title' => 'SQ001',
            'question' => 'Subquestion',
            'scale_id' => '0'
        );

        $subquestionsRestult = array_values($result['subquestions'])[0];
        $this->assertSame($subquestions, $subquestionsRestult, 'The returned subquestion is not correct.');

        //Checking other properties
        $this->assertSame('F', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertSame('4', $result['question_order'], 'The question order is not correct.');
    }

    public function testGetArrayYesNoUncertainQuestionProperties()
    {

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'G01Q05'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertSame('No available answer options', $result['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('C', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertSame('5', $result['question_order'], 'The question order is not correct.');
    }

    public function testGetListQuestionProperties()
    {
        $englishAnswerOptions = array(
            'AO01' => array(
                'answer' => 'Option one',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '0'
            ),
            'AO03' => array(
                'answer' => 'Option two',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '1'
            ),
            'AO02' => array(
                'answer' => 'Option three',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '2'
            )
        );

        $spanishAnswerOptions = array(
            'AO01' => array(
                'answer' => 'Opción uno',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '0'
            ),
            'AO03' => array(
                'answer' => 'Opción dos',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '1'
            ),
            'AO02' => array(
                'answer' => 'Opción tres',
                'assessment_value' => '0',
                'scale_id' => '0',
                'order' => '2'
            )
        );

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('title' => 'G01Q06'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);
        $spanishResult = $this->handler->get_question_properties($sessionKey, $qid, null, 'es');

        //Checking options
        $this->assertSame($englishAnswerOptions, $result['answeroptions'], 'The options were not returned correctly.');
        $this->assertSame($spanishAnswerOptions, $spanishResult['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('L', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertSame('6', $result['question_order'], 'The question order is not correct.');
    }
}
