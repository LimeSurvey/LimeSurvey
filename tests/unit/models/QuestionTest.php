<?php

namespace ls\tests;

use Question;
use PHPUnit\Framework\TestCase;

class QuestionTest extends BaseModelTestCase
{
    protected $modelClassName = Question::class;

    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.common_helper', true);
    }

    /**
     * Test getting the theme from a new (unsaved) question
     */
    public function testGetQuestionThemeOnNewQuestion()
    {
        $question = new Question();
        $question->type = "S";
        $question->question_theme_name = 'browserdetect';

        $questionTheme = $question->questionTheme;

        $this->assertNotEmpty($questionTheme);
        $this->assertEquals("browserdetect", $questionTheme->name);
    }

    /**
     * Test getting the theme from a saved question
     */
    public function testGetQuestionThemeOnSavedQuestion()
    {
        $question = new Question();
        $question->type = "M";
        $question->question_theme_name = 'bootstrap_buttons_multi';
        $question->save();

        $questionTheme = $question->questionTheme;

        $this->assertNotEmpty($questionTheme);
        $this->assertEquals("bootstrap_buttons_multi", $questionTheme->name);
    }

    /**
     * Test validating a question theme name
     * when question type is set to null.
     */
    public function testValidateQuestionThemeNameNoType()
    {
        $question = new Question();
        $question->type = null;

        $result = $question->questionThemeNameValidator();
        $this->assertNull($result, 'An invalid validation result was expected since the question type was set to null.');
    }

    /**
     * Test validating a question theme name
     * when evaluating a child question.
     */
    public function testValidateChildQuestionThemeName()
    {
        $question = new Question();
        $question->parent_qid = 1;

        $result = $question->questionThemeNameValidator();
        $this->assertNull($result, 'An invalid validation result was expected since a child question is being validated.');
    }

    /**
     * Test getting a default theme name from an invalid type.
     */
    public function testGetDefaultQuestionThemeNameFromInvalidType()
    {
        $question = new Question();
        $question->type = 'Test';

        $themeName = $question->questionThemeNameValidator();
        $this->assertNull($themeName, 'An invalid validation result was expected since "Test" question type does not exist.');
    }

    /**
     * Test getting the core theme name.
     */
    public function testGetCoreQuestionThemeName()
    {
        $question = new Question();
        $question->type = "M";
        $question->question_theme_name = 'core';

        $themeName = $question->questionThemeNameValidator();
        $this->assertSame('multiplechoice', $themeName, 'Incorrect core question theme name.');
    }

    /**
     * Test validating question type
     * and question theme name against data base
     */
    public function testValidateQuestionThemeNameAgainstDataBase()
    {
        $question = new Question();
        $question->type = "M";
        $question->question_theme_name = 'image_select-multiplechoice';

        $themeName = $question->questionThemeNameValidator();
        $this->assertSame('image_select-multiplechoice', $themeName, 'Question type and theme name correspond to a theme in data base.');
    }

    /**
     * Testing that question type and question theme name
     * don't match any theme in data base. 
     * Expecting to use default theme name from type
     */
    public function testValidateQuestionThemeNameMismatch()
    {
        $question = new Question();
        $question->type = "M";
        $question->question_theme_name = 'longfreetext';

        $themeName = $question->questionThemeNameValidator();
        $this->assertSame('multiplechoice', $themeName, 'Question theme was not auto corrected and derived from the type\'s default theme.');
    }
}
