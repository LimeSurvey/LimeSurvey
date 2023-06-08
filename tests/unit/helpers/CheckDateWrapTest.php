<?php

namespace ls\tests;

class CheckDateWrapTest extends TestBaseClass
{
    /**
     * Testing that exprmgr_checkdate evaluates
     * the month parameter correctly.
     *
     * The month parameter must be an integer value
     * or an integer numeric string.
     */
    public function testMonthCheck()
    {
        $check_string = exprmgr_checkdate('June', 07, 2023);
        $this->assertFalse($check_string, 'Unexpected evaluation result, the month parameter should not be a string.');

        $check_numeric = exprmgr_checkdate('06', 07, 2023);
        $this->assertTrue($check_numeric, 'Unexpected evaluation result, the month parameter can be a numeric value.');

        $check_int = exprmgr_checkdate(6, 07, 2023);
        $this->assertTrue($check_int, 'Unexpected evaluation result, the month parameter can be an integer.');
    }

    /**
     * Testing that exprmgr_checkdate evaluates
     * the day parameter correctly.
     *
     * The day parameter must be an integer value
     * or an integer numeric string.
     */
    public function testDayCheck()
    {
        $check_string = exprmgr_checkdate(06, '7th.', 2023);
        $this->assertFalse($check_string, 'Unexpected evaluation result, the day parameter should not be a string.');

        $check_numeric = exprmgr_checkdate(06, '7', 2023);
        $this->assertTrue($check_numeric, 'Unexpected evaluation result, the day parameter can be a numeric value.');

        $check_int = exprmgr_checkdate(06, 07, 2023);
        $this->assertTrue($check_int, 'Unexpected evaluation result, the day parameter can be an integer.');
    }

    /**
     * Testing that exprmgr_checkdate evaluates
     * the year parameter correctly.
     *
     * The year parameter must be an integer value
     * or an integer numeric string.
     */
    public function testYearCheck()
    {
        $check_string = exprmgr_checkdate(06, 07, 'Twenty Twenty-three');
        $this->assertFalse($check_string, 'Unexpected evaluation result, the year parameter should not be a string.');

        $check_numeric = exprmgr_checkdate(06, 07, '2023');
        $this->assertTrue($check_numeric, 'Unexpected evaluation result, the year parameter can be a numeric value.');

        $check_int = exprmgr_checkdate(06, 07, 2023);
        $this->assertTrue($check_int, 'Unexpected evaluation result, the year parameter can be an integer.');
    }

    /**
     * Testing the result of checkdate.
     */
    public function testCheckdate()
    {
        $check_numercic = exprmgr_checkdate('06', '07', '2023');
        $this->assertTrue($check_numercic, 'Unexpected evaluation result, all parameters are correct.');

        $check_int = exprmgr_checkdate(06, 07, 2023);
        $this->assertTrue($check_int, 'Unexpected evaluation result, all paramaters are correct.');

        $check_wrong_order = exprmgr_checkdate(15, 07, 2023);
        $this->assertFalse($check_wrong_order, 'Unexpected evaluation result, the parameter order is incorrect.');
    }
}
