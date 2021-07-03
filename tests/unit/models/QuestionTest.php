<?php

namespace ls\tests;

use Question;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    public static function setupBeforeClass(): void
    {
        \Yii::import('application.helpers.common_helper', true);
    }

    /**
     * Test getting the theme from a new (unsaved) question
     */
    public function testGetQuestionThemeOnNewQuestion()
    {
        // Test without setting a question theme name.
        // Should return the base theme for the question type.
        $question = new Question();
        $question->type = "S";

        $questionTheme = $question->questionTheme;

        $this->assertNotEmpty($questionTheme);
        $this->assertEquals("shortfreetext", $questionTheme->name);

        // Test after setting a question theme name
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
        $question->question_theme_name = 'bootstrap_buttons';
        $question->save();

        $questionTheme = $question->questionTheme;

        $this->assertNotEmpty($questionTheme);
        $this->assertEquals("bootstrap_buttons", $questionTheme->name);
    }
}
