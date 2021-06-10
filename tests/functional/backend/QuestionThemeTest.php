<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClassWeb;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\LocalFileDetector;

/**
 *
 */
class QuestionThemeTest extends TestBaseClassWeb
{
    public function testImportQuestionTheme()
    {
        // Login to amdin
        // Go to theme page
        // Click "Import"
        // Choose zip file by tpartner
        // Check result in database
        // Check result in filesystem
    }

    /**
     * @depends testImportQuestionTheme
     */
    public function testSelectQuestionThemeInQuestionEditor()
    {
        // Import survey with one group and question
        // Go to question editor
        // Select question theme
        // Check that all custom attributes are displayed
        // Add values to custom attributes
        // Save question
        // Check database
    }

    /**
     * @depends testSelectQuestionThemeInQuestionEditor
     */
    public function testPreviewQuestionThemeQuestion()
    {
        // Use previous survey
        // Activate survey
        // Execute survey
        // Answer question using question theme
        // Submit
        // Check result in database
    }
}
