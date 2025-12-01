<?php

namespace ls\tests;

/**
 * Tests for the translateLinks function.
 */

class TranslateLinksTest extends TestBaseClass
{
    private static $originalPublicUrl;
    private static $originalBaseUrl;

    public static function setupBeforeClass(): void
    {
        parent::setupBeforeClass();
        \Yii::import('application.helpers.common_helper', true);
        self::$originalPublicUrl = \Yii::app()->getConfig('publicUrl');
        self::$originalBaseUrl = \Yii::app()->getRequest()->getBaseUrl();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        \Yii::app()->setConfig('publicUrl', self::$originalPublicUrl);
        \Yii::app()->getRequest()->setBaseUrl(self::$originalBaseUrl);
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
        $linkString = 'https://limesurvey.org/upload/surveys/111111/files/file.ext';

        $link = translateLinks('survey', '333333', '444444', $linkString);

        $this->assertEquals($linkString, $link);

        //Link string should point to labels, not to surveys
        $linkString = 'https://limesurvey.org/upload/surveys/111111/files/file.ext';

        $link = translateLinks('label', '111111', '222222', $linkString);

        $this->assertEquals($linkString, $link);

        //Type should be survey or label
        $linkString = 'https://limesurvey.org/upload/surveys/111111/files/file.ext';

        $link = translateLinks('other', '111111', '222222', $linkString);

        $this->assertEquals($linkString, $link);
    }

    /**
     * Translating label links.
     */
    public function testTranslateLabelLinks(): void
    {
        $absoluteBasePublicUrl = rtrim(\Yii::app()->getPublicBaseUrl(true), "/") . "/";
        $basePublicUrl = rtrim(\Yii::app()->getConfig("publicurl"), "/") . "/";

        //HTTP only
        $linkString = 'http://limesurvey.org/upload/labels/111111/files/file.ext';

        $link = translateLinks('label', '111111', '222222', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/labels/222222/files/file.ext';
        $this->assertEquals($expected, $link);

        //HTTPS
        $linkString = 'https://limesurvey.org/upload/labels/333333/files/file.ext';

        $link = translateLinks('label', '333333', '444444', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/labels/444444/files/file.ext';
        $this->assertEquals($expected, $link);

        //Url with dashes and or underscores
        $linkString = 'https://lime-survey.org/lime_survey/upload/labels/333333/files/file.ext';

        $link = translateLinks('label', '333333', '444444', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/labels/444444/files/file.ext';
        $this->assertEquals($expected, $link);

        // Relative URL
        $linkString = '/upload/labels/111111/files/file.ext';

        $link = translateLinks('label', '111111', '222222', $linkString);

        $expected = $basePublicUrl . 'upload/labels/222222/files/file.ext';
        $this->assertEquals($expected, $link);

        // Multiple URLs
        $linkString = 'http://limesurvey.org/upload/labels/111111/files/file.ext<br>/upload/labels/111111/files/file.ext';

        $link = translateLinks('label', '111111', '222222', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/labels/222222/files/file.ext<br>' . $basePublicUrl . 'upload/labels/222222/files/file.ext';
        $this->assertEquals($expected, $link);

        //Trying to translate a local path
        $linkString = '/var/www/html/limesurvey/upload/labels/555555/files/file.ext';

        $link = translateLinks('label', '555555', '666666', $linkString, true);

        $uploadDir = \Yii::app()->getConfig("uploaddir") . '/labels/666666/files/file.ext';
        $this->assertNotEquals($uploadDir, $link);
    }

    /**
     * Translating survey links.
     */
    public function testTranslateSurveyLinks(): void
    {
        $absoluteBasePublicUrl = rtrim(\Yii::app()->getPublicBaseUrl(true), "/") . "/";
        $basePublicUrl = rtrim(\Yii::app()->getConfig("publicurl"), "/") . "/";

        //HTTP only
        $linkString = 'http://limesurvey.org/lime_survey/upload/surveys/111111/files/file.ext';

        $link = translateLinks('survey', '111111', '222222', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/surveys/222222/files/file.ext';
        $this->assertEquals($expected, $link);

        //HTTPS
        $linkString = 'https://limesurvey.org/upload/surveys/333333/files/file.ext';

        $link = translateLinks('survey', '333333', '444444', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/surveys/444444/files/file.ext';
        $this->assertEquals($expected, $link);

        //Url with dashes and or underscores
        $linkString = 'https://lime-survey.org/upload/surveys/333333/files/file.ext';

        $link = translateLinks('survey', '333333', '444444', $linkString);

        $expected = $absoluteBasePublicUrl . 'upload/surveys/444444/files/file.ext';
        $this->assertEquals($expected, $link);

        // Relative URL
        $linkString = '/lime_survey/upload/surveys/111111/files/file.ext';

        $link = translateLinks('survey', '111111', '222222', $linkString);

        $expected = $basePublicUrl . 'upload/surveys/222222/files/file.ext';
        $this->assertEquals($expected, $link);

        //Translating a local path
        $linkString = '/var/www/html/limesurvey/upload/surveys/555555/files/file.ext';

        $link = translateLinks('survey', '555555', '666666', $linkString, true);

        $uploadDir = \Yii::app()->getConfig("uploaddir") . '/surveys/666666/files/file.ext';
        $this->assertEquals($uploadDir, $link);
    }

    /**
     * Different public URL configurations
     */
    public function testTranslateLinksWithDifferentPublicUrlConfigurations()
    {
        $linkString = 'http://limesurvey.org/upload/labels/111111/files/file.ext<br>/upload/labels/111111/files/file.ext';

        \Yii::app()->getRequest()->setBaseUrl('www.example.com');

        // Default public url
        \Yii::app()->setConfig('publicUrl', \Yii::app()->baseUrl . '/');

        $link = translateLinks('label', '111111', '222222', $linkString);

        $expected = 'http://www.example.com/upload/labels/222222/files/file.ext<br>/upload/labels/222222/files/file.ext';
        $this->assertEquals($expected, $link);


        // Absolute public url
        \Yii::app()->setConfig('publicurl', 'http://public.example.com/');

        $link = translateLinks('label', '111111', '222222', $linkString);

        $expected = 'http://public.example.com/upload/labels/222222/files/file.ext<br>http://public.example.com/upload/labels/222222/files/file.ext';
        $this->assertEquals($expected, $link);
    }
}
