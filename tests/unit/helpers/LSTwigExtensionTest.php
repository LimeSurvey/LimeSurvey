<?php

namespace ls\tests;

/**
 * Test LS_Twig_Extension functions.
 */
class LSTwigExtensionTest extends TestBaseClass
{
    /**
     * Test isAbsoluteUrl function.
     */
    public function testIsAbsoluteUrlFunction()
    {
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
            $result = \LS_Twig_Extension::isAbsoluteUrl($validCase);
            $this->assertTrue($result);
        }

        $invalidCases = [
            'Simple text',
            'Word',
            'relative/url',
        ];
        foreach ($invalidCases as $invalidCase) {
            $result = \LS_Twig_Extension::isAbsoluteUrl($invalidCase);
            $this->assertNotTrue($result);
        }
    }
}
