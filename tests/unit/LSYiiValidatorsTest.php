<?php

namespace ls\tests;

/**
 * Test LSYii_Validators class.
 */
class LSYiiValidatorsTest extends TestBaseClass
{
    private static $cases = array();

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$cases['specialChars'] = array(
            array(
                'string'  => '&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;',
                'decoded' => '<script>alert("XSS");</script>'
            ),
            array(
                'string'  => 'one%20%26%20two',
                'decoded' => 'one & two'
            ),
            array(
                'string'  => '&#60;script&#62;alert(1);&#60;/script&#62;',
                'decoded' => '<script>alert(1);</script>'
            ),
            array(
                'string'  => '<p>Espa&#241;ol.</p>',
                'decoded' => '<p>Español.</p>'
            ),
        );

        self::$cases['unsafe'] = array(
            'jav&#x09;ascript:alert(\'XSS\');',
            'javascript:alert(\'XSS\');',
            'JavaSCRIPT:Alert(\'XSS\');',
            "jav&#x09;ascript:alert('XSS');",
            "jav&#x0A;ascript:alert('XSS');",
            "jav&#x0D;ascript:alert('XSS');",
            "java\0script:alert('XSS');",

        );

        self::$cases['safe'] = array(
            'http://example.com',
            'https://example.com',
        );
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    /**
     * Test filtering of html_entity_decode.
     */
    public function testHtmlEntityDecodeFilter()
    {
        // First, we define the cases to test. Array keys are the strings to filter, and values are the decoded result
        $cases = [
            "html_entity_decode('&amp;')" => "html_entity_decode('&amp;')", // Not an expression, so it shouldn't be changed.
            "{html_entity_decode('&amp;')}" => "{('&amp;')}",   // Used as a function in an expression, so it should be removed.
            "{join(\"&#123;\",'html_entity_decode(\"&amp;amp;\")',\"&#125;\")}" => "{join(\"{\",'html_entity_decode(\"&amp;amp;\")',\"}\")}",   // Inside a function but as a string, so it's not removed.
        ];

        $validator = new \LSYii_Validators();

        // Test each case
        foreach ($cases as $string => $decoded) {
            $actual = $validator->xssFilter($string);
            $this->assertEquals($decoded, $actual);
        }
    }

    /**
     * Testing that URL encoded characters and html entities are decoded correctly.
     */
    public function testTreatSpecialChars()
    {
        $cases = self::$cases['specialChars'];

        foreach ($cases as $key => $case) {
            $this->assertSame($case['decoded'], \LSYii_Validators::treatSpecialChars($case['string']), 'Undecoded filtered string. Case key: ' . $key);
        }
    }

    /**
     * Testing that unsafe schemes are detected.
     */
    public function testHasUnsafeScheme()
    {
        $unsafeCases = self::$cases['unsafe'];

        foreach ($unsafeCases as $key => $case) {
            $url = \LSYii_Validators::treatSpecialChars($case);
            $cleanUrl = \LSYii_Validators::removeInvisibleChars($url);
            $this->assertTrue(\LSYii_Validators::hasUnsafeScheme($cleanUrl), 'Undecoded result in case key ' . $key . '. ' . $url . ' is actually safe.');
        }

        $safeCases = self::$cases['safe'];

        foreach ($safeCases as $key => $case) {
            $url = \LSYii_Validators::treatSpecialChars($case);
            $this->assertFalse(\LSYii_Validators::hasUnsafeScheme($url), 'Undecoded result in case key ' . $key . '. ' . $url . ' is actually unsafe.');
        }
    }

    /**
     * Testing that XSS potentially dangerous urls are detected.
     */
    public function testIsXssUrl()
    {
        $unsafeCases = self::$cases['unsafe'];

        foreach ($unsafeCases as $key => $case) {
            $this->assertTrue(\LSYii_Validators::isXssUrl($case), 'Undecoded result in case key ' . $key . '. ' . $case . ' is actually unsafe.');
        }

        $safeCases = self::$cases['safe'];

        foreach ($safeCases as $key => $case) {
            $this->assertFalse(\LSYii_Validators::isXssUrl($case), 'Undecoded result in case key ' . $key . '. ' . $case . ' is actually safe.');
        }
    }

    /**
     * Testing that invisible characters are removed.
     */
    public function testRemoveInvisibleChars()
    {

        $cases = self::$cases['unsafe'];

        foreach ($cases as $case) {
            $string = \LSYii_Validators::treatSpecialChars($case);
            $result = \LSYii_Validators::removeInvisibleChars($string);
            $this->assertEqualsIgnoringCase('javascript:alert(\'XSS\');', $result, 'Undecoded result, apparently not all invisible chars were removed.');
        }
    }
}
