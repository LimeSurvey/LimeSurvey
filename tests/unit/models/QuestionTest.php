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
     * Test getting a default theme name from default type
     */
    public function testGetDefaultQuestionThemeNameFromType()
    {
        $question = new Question();

        $question->questionThemeNameValidator();
        $this->assertSame('T', $question->type, 'Incorrect default question type');
        $this->assertSame('longfreetext', $question->question_theme_name, 'Incorrect default question theme name.');
    }

    /**
     * Test getting a default theme name from an invalid type.
     */
    public function testGetDefaultQuestionThemeNameFromInvalidType()
    {
        $question = new Question();
        $question->type = 'Test';

        $question->questionThemeNameValidator();
        $this->assertNull($question->question_theme_name, 'An invalid validation result was expected since "Z" question type does not exist.');
    }

    /**
     * Test getting the core theme name.
     */
    public function testGetCoreQuestionThemeName()
    {
        $question = new Question();
        $question->type = "M";
        $question->question_theme_name = 'core';

        $question->questionThemeNameValidator();
        $this->assertSame('multiplechoice', $question->question_theme_name, 'Incorrect core question theme name.');
    }
}
