<?php

namespace ls\tests;

/**
 * Tests for the checkOldLinks function.
 */

class CheckOldLinksTest extends TestBaseClass
{
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
    }

    public function testEmptyLink()
    {
        $oldLinks = checkOldLinks('survey', '111111', '');
        $this->assertFalse($oldLinks, 'The link should be empty.');
    }

    public function testUnknownType()
    {
        $oldLinks = checkOldLinks('encuesta', '111111', '');
        $this->assertFalse($oldLinks, 'Link type is not survey or label.');
    }

    public function testNoSurveyOldLinks()
    {
        $newLink = 'http://' . getenv('DOMAIN') . '/upload/surveys/222222/';
        $oldLinks = checkOldLinks('survey', '111111', $newLink);

        $this->assertFalse((bool)$oldLinks, 'The url ' . $newLink . ' is an old link.');
    }

    public function testSurveyOldLinks()
    {
        $newLink = 'http://' . getenv('DOMAIN') . '/upload/surveys/2222/';
        $oldLinks = checkOldLinks('survey', '2222', $newLink);

        $this->assertTrue((bool)$oldLinks, 'The url ' . $newLink . ' is not an old link.');
    }

    public function testNoLabelOldLinks()
    {
        $newLink = 'http://' . getenv('DOMAIN') . '/upload/labels/222222/';
        $oldLinks = checkOldLinks('label', '111111', $newLink);

        $this->assertFalse((bool)$oldLinks, 'The url ' . $newLink . ' is an old link.');
    }

    public function testLabelOldLinks()
    {
        $newLink = 'http://' . getenv('DOMAIN') . '/upload/labels/2222/';
        $oldLinks = checkOldLinks('label', '2222', $newLink);

        $this->assertTrue((bool)$oldLinks, 'The url ' . $newLink . ' is not an old link.');
    }
}
