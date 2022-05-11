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
}
