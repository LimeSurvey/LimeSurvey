<?php

namespace ls\tests;

/**
 * Tests for the translateLinks function.
 */

class TranslateLinksTest extends TestBaseClass
{
    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
    }

    /**
     * Tests for the link string parameter.
     */
    public function testLinkString(): void
    {
        //Empty string
        $link = translateLinks('survey', '111111', '222222', '');

        $this->assertEmpty($link);

        //Old survey ID should be in the link string
        $linkString = 'https://gitit-tech.com/upload/surveys/111111/files/file.ext';

        $link = translateLinks('survey', '333333', '444444', $linkString);

        $this->assertEquals($link, $linkString);

        //Link string should point to labels, not to surveys
        $linkString = 'https://gitit-tech.com/upload/surveys/111111/files/file.ext';

        $link = translateLinks('label', '111111', '222222', $linkString);

        $this->assertEquals($link, $linkString);

        //Type should be survey or label
        $linkString = 'https://gitit-tech.com/upload/surveys/111111/files/file.ext';

        $link = translateLinks('other', '111111', '222222', $linkString);

        $this->assertEquals($link, $linkString);
    }

    /**
     * Translating label links.
     */
    public function testTranslateLabelLinks(): void
    {
        //HTTP only
        $linkString = 'http://gitit-tech.com/upload/labels/111111/files/file.ext';

        $link = translateLinks('label', '111111', '222222', $linkString);

        $publicUrl = \Yii::app()->getConfig("publicurl") . 'upload/labels/222222/files/file.ext';
        $this->assertEquals($link, $publicUrl);

        //HTTPS
        $linkString = 'https://gitit-tech.com/upload/labels/333333/files/file.ext';

        $link = translateLinks('label', '333333', '444444', $linkString);

        $publicUrl = \Yii::app()->getConfig("publicurl") . 'upload/labels/444444/files/file.ext';
        $this->assertEquals($link, $publicUrl);

        //Url with dashes and or underscores
        $linkString = 'https://lime-survey.org/lime_survey/upload/labels/333333/files/file.ext';

        $link = translateLinks('label', '333333', '444444', $linkString);

        $publicUrl = \Yii::app()->getConfig("publicurl") . 'upload/labels/444444/files/file.ext';
        $this->assertEquals($link, $publicUrl);

        //Trying to translate a local path
        $linkString = '/var/www/html/limesurvey/upload/labels/555555/files/file.ext';

        $link = translateLinks('label', '555555', '666666', $linkString, true);

        $uploadDir = \Yii::app()->getConfig("uploaddir") . '/labels/666666/files/file.ext';
        $this->assertNotEquals($link, $uploadDir);
    }

    /**
     * Translating survey links.
     */
    public function testTranslateSurveyLinks(): void
    {
        //HTTP only
        $linkString = 'http://gitit-tech.com/lime_survey/upload/surveys/111111/files/file.ext';

        $link = translateLinks('survey', '111111', '222222', $linkString);

        $publicUrl = \Yii::app()->getConfig("publicurl") . 'upload/surveys/222222/files/file.ext';
        $this->assertEquals($link, $publicUrl);

        //HTTPS
        $linkString = 'https://gitit-tech.com/upload/surveys/333333/files/file.ext';

        $link = translateLinks('survey', '333333', '444444', $linkString);

        $publicUrl = \Yii::app()->getConfig("publicurl") . 'upload/surveys/444444/files/file.ext';
        $this->assertEquals($link, $publicUrl);

        //Url with dashes and or underscores
        $linkString = 'https://lime-survey.org/upload/surveys/333333/files/file.ext';

        $link = translateLinks('survey', '333333', '444444', $linkString);

        $publicUrl = \Yii::app()->getConfig("publicurl") . 'upload/surveys/444444/files/file.ext';
        $this->assertEquals($link, $publicUrl);

        //Translating a local path
        $linkString = '/var/www/html/limesurvey/upload/surveys/555555/files/file.ext';

        $link = translateLinks('survey', '555555', '666666', $linkString, true);

        $uploadDir = \Yii::app()->getConfig("uploaddir") . '/surveys/666666/files/file.ext';
        $this->assertEquals($link, $uploadDir);
    }
}
