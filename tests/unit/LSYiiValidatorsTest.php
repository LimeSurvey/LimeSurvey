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
     * Testing that the xssfilter attribute will always be
     * false for super admin users even if filterxsshtml is
     * changed.
     */
    public function testXssFilterAttributeForSuperAdmin()
    {
        //Mocking super admin login.
        \Yii::app()->session['loginID'] = 1;
        $superAdminValidator = new \LSYii_Validators();

        $this->assertTrue(\Yii::app()->getConfig('filterxsshtml'), 'filterxsshtml should be true by default.');
        $this->assertFalse($superAdminValidator->xssfilter, 'The xssfilter attribute should be false for super admins.');

        //Changing filterxsshtml.
        \Yii::app()->setConfig('filterxsshtml', false);
        $newSuperAdminValidator = new \LSYii_Validators();

        $this->assertFalse(\Yii::app()->getConfig('filterxsshtml'), 'filterxsshtml was just changed to false.');
        $this->assertFalse($newSuperAdminValidator->xssfilter, 'The xssfilter attribute should be false for super admins.');

        //Returning to original values.
        \Yii::app()->setConfig('filterxsshtml', true);
        \Yii::app()->session['loginID'] = null;
    }

    /**
     * Testing that the xssfilter attribute varies
     * for regular users depending on filterxsshtml.
     */
    public function testXssFilterAttributeForRegularUsers()
    {
        //Create user.
        $newPassword = createPassword();
        $userId = \User::insertUser('test_user', $newPassword, 'John Doe', 1, 'jd@mail.com');

        //Mocking regular user login.
        \Yii::app()->session['loginID'] = $userId;
        $regularUserValidator = new \LSYii_Validators();

        $this->assertTrue(\Yii::app()->getConfig('filterxsshtml'), 'filterxsshtml should be true by default.');
        $this->assertTrue($regularUserValidator->xssfilter, 'The xssfilter attribute should be true for regular users.');

        //Changing filterxsshtml.
        \Yii::app()->setConfig('filterxsshtml', false);
        $newRegularUserValidator = new \LSYii_Validators();

        $this->assertFalse(\Yii::app()->getConfig('filterxsshtml'), 'filterxsshtml was just changed to false.');
        $this->assertFalse($newRegularUserValidator->xssfilter, 'The xssfilter attribute should be false for regular users with filterxsshtml set to false.');

        //Returning to original values.
        \Yii::app()->setConfig('filterxsshtml', true);
        \Yii::app()->session['loginID'] = null;

        //Delete user.
        $user = \User::model()->findByPk($userId);
        $user->delete();
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
            array(
                'string'   => '<title>html_entity_decode("<script>alert("Test")</script>123456")</title>',
                'expected' => 'html_entity_decode("123456")'
            )
        );

        foreach ($cases as $case) {
            $this->assertSame($case['expected'], $validator->xssFilter($case['string']), 'Unexpected filtered dangerous string.');
        }
    }

    /**
     * Testing that safe HTML tags are not removed.
     */
    public function testSafeHtml()
    {
        $validator = new \LSYii_Validators();

        $cases = array(
            '<p>Paragraph</p>',
            '<strong>Text</strong>',
            '<span>Some text</span>',
        );

        foreach ($cases as $case) {
            $this->assertSame($case, $validator->xssFilter($case), 'Unexpected filtered safe HTML tags.');
        }
    }
}
