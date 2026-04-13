<?php

namespace ls\tests;

/**
 * Test check_absolute_url function.
 */
class CheckAbsoluteUrlTest extends TestBaseClass
{
    /**
     * Test check_absolute_url function.
     */
    public function testCheckAbsoluteUrlFunction()
    {
        \Yii::import('application.helpers.sanitize_helper', true);
        $validCases = [
            'http://127.0.0.1/limesurvey/image.png',
            'http://localhost/limesurvey/image.png',
            'http://localhost:8080/limesurvey/image.png',
            'http://example.com/limesurvey/image.jpeg',
            'https://example.com/limesurvey/image.png',
            'https://مثال.إختبار/limesurvey/image.png',
            'https://例子.测试/limesurvey/image.png',
            'https://उदाहरण.परीक्षा/limesurvey/image.png',
            '//example.com/limesurvey/image.png',
            '/limesurvey/image.png',
            '/limesurvey/مثال.png',
            '/limesurvey/例子.png',
            '/limesurvey/उदाहरण.png',
        ];
        foreach ($validCases as $validCase) {
            $result = check_absolute_url($validCase);
            $this->assertTrue($result);
        }

        $invalidCases = [
            'Simple text',
            'Word',
            'relative/url',
        ];
        foreach ($invalidCases as $invalidCase) {
            $result = check_absolute_url($invalidCase);
            $this->assertNotTrue($result);
        }
    }
}
