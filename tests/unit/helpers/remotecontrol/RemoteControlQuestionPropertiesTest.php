<?php

namespace ls\tests\unit\helpers\remotecontrol;

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

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q02'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid);

        //Checking options
        $this->assertEquals($answerOptionsByScaleId, array_map('get_object_vars', $result['answeroptions']), 'The options were not returned correctly.');
        //Checking other properties
        $this->assertEquals('1', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('2', $result['question_order'], 'The question order is not correct.');
        // Checking L10n properties
        $this->assertEquals('Dual scale question.', $result['question'], 'The question text is not correct.');
        $this->assertIsArray($result['questionl10ns'], 'The questionl10ns are included');
        $this->assertEquals('Dual scale question.', $result['questionl10ns']['question'], 'The question text is not correct inside questionl10ns.');

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

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q02'));
        $qid = $question->qid;

        //Get properties in Spanish
        $result = $this->handler->get_question_properties($sessionKey, $qid, null, 'es');

        //Checking options
        $this->assertEquals($answerOptionsByScaleId, array_map('get_object_vars', $result['answeroptions']), 'The options were not returned correctly.');
        //Checking other properties
        $this->assertEquals('1', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('2', $result['question_order'], 'The question order is not correct.');
        // Checking L10n properties
        $this->assertEquals('Pregunta de doble escala.', $result['question'], 'The question text is not correct.');
        $this->assertIsArray($result['questionl10ns'], 'The questionl10ns are included');
        $this->assertEquals('Pregunta de doble escala.', $result['questionl10ns']['question'], 'The question text is not correct inside questionl10ns.');
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

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q03'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertEquals($answerOptions, (array) $result['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('H', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('3', $result['question_order'], 'The question order is not correct.');
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

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q04'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertEquals($answerOptions, (array) $result['answeroptions'], 'The options were not returned correctly.');

        //Checking subquestions
        $subquestions = array(
            'title' => 'SQ001',
            'question' => 'Subquestion',
            'scale_id' => '0'
        );

        $subquestionsResult = array_values($result['subquestions'])[0];
        $this->assertEquals($subquestions, $subquestionsResult, 'The returned subquestion is not correct.');

        //Checking other properties
        $this->assertSame('F', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('4', $result['question_order'], 'The question order is not correct.');
    }

    public function testGetArrayYesNoUncertainQuestionProperties()
    {

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q05'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertSame('No available answer options', $result['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('C', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('5', $result['question_order'], 'The question order is not correct.');
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

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q06'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);
        $spanishResult = $this->handler->get_question_properties($sessionKey, $qid, null, 'es');

        //Checking options
        $this->assertEquals($englishAnswerOptions, (array) $result['answeroptions'], 'The options were not returned correctly.');
        $this->assertEquals($spanishAnswerOptions, (array) $spanishResult['answeroptions'], 'The options were not returned correctly.');
        //Checking other properties
        $this->assertSame('L', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('6', $result['question_order'], 'The question order is not correct.');
        // Checking L10n properties
        $this->assertEquals('List question', $result['question'], 'The question text is not correct.');
        $this->assertIsArray($result['questionl10ns'], 'The questionl10ns are included');
        $this->assertEquals('List question', $result['questionl10ns']['question'], 'The question text is not correct inside questionl10ns.');
    }

    public function testGetMultipleChoiceQuestionProperties()
    {
        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());

        $question = \Question::model()->findByAttributes(array('sid' => self::$surveyId,'title' => 'G01Q07'));
        $qid = $question->qid;

        $result = $this->handler->get_question_properties($sessionKey, $qid, null);

        //Checking options
        $this->assertEquals('No available answer options', $result['answeroptions'], 'The options were not returned correctly.');

        //Checking subquestions
        $englishSubquestions = array(
            array(
                'title' => 'SQ001',
                'question' => 'Option one',
                'scale_id' => '0'
            ),
            array(
                'title' => 'SQ002',
                'question' => 'Option two',
                'scale_id' => '0'
            ),
            array(
                'title' => 'SQ003',
                'question' => 'Option three',
                'scale_id' => '0'
            ),
            array(
                'title' => 'SQ004',
                'question' => 'Option four',
                'scale_id' => '0'
            )
        );

        $englishSubquestionsResult = array_values($result['subquestions']);

        $this->assertEquals($englishSubquestions, $englishSubquestionsResult, 'The returned subquestions are not correct.');

        $spanishSubquestions = array(
            array(
                'title' => 'SQ001',
                'question' => 'Opción uno',
                'scale_id' => '0'
            ),
            array(
                'title' => 'SQ002',
                'question' => 'Opción dos',
                'scale_id' => '0'
            ),
            array(
                'title' => 'SQ003',
                'question' => 'Opción tres',
                'scale_id' => '0'
            ),
            array(
                'title' => 'SQ004',
                'question' => 'Opción cuatro',
                'scale_id' => '0'
            )
        );

        $spanishResult = $this->handler->get_question_properties($sessionKey, $qid, null, 'es');
        $spanishSubquestionsResult = array_values($spanishResult['subquestions']);

        $title = array_column($spanishSubquestionsResult, 'title');
        array_multisort($title, SORT_ASC, $spanishSubquestionsResult);

        $this->assertEquals($spanishSubquestions, $spanishSubquestionsResult, 'The returned subquestions (multilanguage) are not correct.');

        //Checking other properties
        $this->assertSame('M', $result['type'], 'The question type is not correct.');
        $this->assertSame('N', $result['mandatory'], 'The question should not be mandatory.');
        $this->assertSame('N', $result['encrypted'], 'The question should not be encrypted.');
        $this->assertEquals('7', $result['question_order'], 'The question order is not correct.');
    }

    /**
     * Answer options and subquestions with the codes 0, 1, 2 must still be
     * returned keyed by code (as JSON object / XML-RPC struct), not as a list.
     */
    public function testGetQuestionPropertiesWithNumericCodes()
    {
        $listQuestion = \Question::model()->findByAttributes(array('sid' => self::$surveyId, 'title' => 'G01Q06'));
        $language = \Survey::model()->findByPk(self::$surveyId)->language;

        $question = new \Question();
        $question->sid = self::$surveyId;
        $question->gid = $listQuestion->gid;
        $question->type = 'F';
        $question->title = 'NumericCodes';
        $question->parent_qid = 0;
        $question->question_order = 99;
        $this->assertTrue($question->save(false), 'The question could not be created.');
        $questionL10n = new \QuestionL10n();
        $questionL10n->qid = $question->qid;
        $questionL10n->language = $language;
        $questionL10n->question = 'Numeric codes';
        $this->assertTrue($questionL10n->save(false), 'The question text could not be created.');

        foreach (array('0', '1') as $order => $code) {
            $subquestion = new \Question();
            $subquestion->sid = self::$surveyId;
            $subquestion->gid = $listQuestion->gid;
            $subquestion->type = 'F';
            $subquestion->title = $code;
            $subquestion->parent_qid = $question->qid;
            $subquestion->question_order = $order;
            $this->assertTrue($subquestion->save(false), 'The subquestion could not be created.');
            $subquestionL10n = new \QuestionL10n();
            $subquestionL10n->qid = $subquestion->qid;
            $subquestionL10n->language = $language;
            $subquestionL10n->question = 'Subquestion ' . $code;
            $this->assertTrue($subquestionL10n->save(false), 'The subquestion text could not be created.');
        }

        foreach (array('0', '1', '2') as $order => $code) {
            $answer = new \Answer();
            $answer->qid = $question->qid;
            $answer->code = $code;
            $answer->sortorder = $order;
            $answer->scale_id = 0;
            $answer->assessment_value = 0;
            $this->assertTrue($answer->save(false), 'The answer option could not be created.');
            $answerL10n = new \AnswerL10n();
            $answerL10n->aid = $answer->aid;
            $answerL10n->language = $language;
            $answerL10n->answer = 'Option ' . $code;
            $this->assertTrue($answerL10n->save(false), 'The answer option text could not be created.');
        }

        $sessionKey = $this->handler->get_session_key($this->getUsername(), $this->getPassword());
        $result = $this->handler->get_question_properties($sessionKey, $question->qid, array('answeroptions', 'available_answers'));

        // JSON-RPC
        $answerOptions = json_decode(json_encode($result['answeroptions']));
        $this->assertIsObject($answerOptions, 'The answer options are not returned keyed by code.');
        $this->assertSame(array('0', '1', '2'), array_map('strval', array_keys(get_object_vars($answerOptions))));
        $this->assertSame('Option 1', $answerOptions->{'1'}->answer);
        $availableAnswers = json_decode(json_encode($result['available_answers']));
        $this->assertIsObject($availableAnswers, 'The available answers are not returned keyed by code.');
        $this->assertSame('Subquestion 1', $availableAnswers->{'1'});

        // XML-RPC: Unchanged, encoded like the array
        \Yii::import('application.libraries.BigData', true);
        ob_start();
        \BigData::xmlrpc_echo($result['answeroptions']);
        $xml = ob_get_clean();
        ob_start();
        \BigData::xmlrpc_echo((array) $result['answeroptions']);
        $this->assertSame(ob_get_clean(), $xml, 'The XML-RPC answer options changed.');
    }
}
