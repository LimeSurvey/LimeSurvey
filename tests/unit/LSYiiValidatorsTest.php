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
     * Testing that the xssfilter attribute varies
     * depending on the user.
     */
    public function testXssFilterAttribute()
    {
        $regularUserValidator = new \LSYii_Validators();

        $isFiltered = $regularUserValidator->xssfilter;
        $this->assertTrue($isFiltered, 'The xssfilter attribute should be true for regular users.');

        \Yii::app()->session['loginID'] = 1;
        $superAdminValidator = new \LSYii_Validators();

        $isFiltered = $superAdminValidator->xssfilter;
        $this->assertFalse($isFiltered, 'The xssfilter attribute should be false for super admins.');
    }

    /**
     * Testing that any script or dangerous HTML is removed.
     */
    public function testXssFilterApplied()
    {
        $validator = new \LSYii_Validators();

        $cases = array(
            array(
                'string'   => '<script>alert(`Test`)</script>',
                'expected' => ''
            ),
            array(
                'string'   => `{join('html_entity_decode("', '<script>alert("Test")</script>")')}`,
                'expected' => ''
            ),
            array(
                'string'   => '<title>html_entity_decode("<script>alert("Test")</script>")</title>',
                'expected' => 'html_entity_decode("")'
            ),
            array(
                'string'   => `{join('html_entity_decode("', '<s', 'cript>alert("Test")</script>")')}`,
                'expected' => ''
            ),
            array(
                'string'   => `{join('html_entity_decode("', '<', 'script>alert("Test")<', '/script>")')`,
                'expected' => ''
            ),
        );

        foreach ($cases as $case) {
            $this->assertSame($case['expected'], $validator->xssFilter($case['string']), 'Unexpected filtered dangerous string.');
        }
    }
}
