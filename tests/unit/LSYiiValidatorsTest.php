<?php

namespace ls\tests;

/**
 * Test LSYii_Validators class.
 */
class LSYiiValidatorsTest extends TestBaseClass
{
    /**
     * Test filtering of html_entity_decode.
     */
    public function testHtmlEntityDecodeFilter()
    {
        // First, we define the cases to test. Array keys are the strings to filter, and values are the expected result
        $cases = [
            "html_entity_decode('&amp;')" => "html_entity_decode('&amp;')", // Not an expression, so it shouldn't be changed.
            "{html_entity_decode('&amp;')}" => "{('&amp;')}",   // Used as a function in an expression, so it should be removed.
            "{join(\"&#123;\",'html_entity_decode(\"&amp;amp;\")',\"&#125;\")}" => "{join(\"{\",'html_entity_decode(\"&amp;amp;\")',\"}\")}",   // Inside a function but as a string, so it's not removed.
        ];

        $validator = new \LSYii_Validators();

        // Test each case
        foreach ($cases as $string => $expected) {
            $actual = $validator->xssFilter($string);
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * Testing that URL encoded characters and html entities are decoded correctly.
     */
    public function testTreatSpecialChars()
    {
        $cases = array(
            array(
                'string'   => '&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;',
                'expected' => '<script>alert("XSS");</script>'
            ),
            array(
                'string'   => 'one%20%26%20two',
                'expected' => 'one & two'
            ),
            array(
                'string'   => '&#60;script&#62;alert(1);&#60;/script&#62;',
                'expected' => '<script>alert(1);</script>'
            ),
            array(
                'string'   => '<p>Espa&#241;ol.</p>',
                'expected' => '<p>Español.</p>'
            ),
            array(
                'string'   => 'jav&#x20;ascript:alert(\'XSS\');',
                'expected' => 'jav ascript:alert(\'XSS\');'
            ),
            array(
                'string'   => 'javascript:alert(1)',
                'expected' => 'javascript:alert(1)'
            ),
            array(
                'string'   => 'JavaSCRIPT:Alert(1);',
                'expected' => 'JavaSCRIPT:Alert(1);'
            ),
            array(
                'string'   => 'javasjavascript:cript:alert(1)',
                'expected' => 'javasjavascript:cript:alert(1)'
            )
        );

        foreach ($cases as $key => $case) {
            $this->assertSame($case['expected'], \LSYii_Validators::treatSpecialChars($case['string']), 'Unexpected filtered string. Case key: ' . $key);
        }
    }

    /**
     * Testing that unsafe schemes are detected.
     */
    public function testHasUnsafeScheme()
    {
        $unsafeCases = array(
            'javascript:alert(1)',
            'JavaSCRIPT:Alert(1);',
            'javasjavascript:cript:alert(1)',
        );

        foreach ($unsafeCases as $key => $case) {
            $url = \LSYii_Validators::treatSpecialChars($case);
            $this->assertTrue(\LSYii_Validators::hasUnsafeScheme($url), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually unsafe.');
        }

        $safeCases = array(
            'http://example.com',
            'https://example.com',
        );

        foreach ($safeCases as $key => $case) {
            $url = \LSYii_Validators::treatSpecialChars($case);
            $this->assertFalse(\LSYii_Validators::hasUnsafeScheme($url), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually safe.');
        }
    }

    /**
     * Testing that XSS potentially dangerous urls are detected.
     */
    public function testIsXssUrl()
    {
        $unsafeCases = array(
            'javascript:alert(1)',
            'JavaSCRIPT:Alert(1);',
            'javasjavascript:cript:alert(1)',
        );

        foreach ($unsafeCases as $key => $case) {
            $this->assertTrue(\LSYii_Validators::isXssUrl($case), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually unsafe.');
        }

        $safeCases = array(
            'http://example.com',
            'https://example.com',
        );

        foreach ($safeCases as $key => $case) {
            $this->assertFalse(\LSYii_Validators::isXssUrl($case), 'Unexpected result in case key ' . $key . '. ' . $case . ' is actually safe.');
        }
    }
}
