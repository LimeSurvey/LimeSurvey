<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test Expression manager PHP function
 *
 * @since 2022-09-30
 * @group em
 */
class ExpressionCoreFunctionsTest extends TestBaseClass
{
    /**
     * @group me
     */
    public function testIntFunction()
    {
        /* array : result must be true */
        $isIntValue = array(
            "3",
            "0",
            "3.00000",
            "300000",
            "000" // situation on 2022-09-30
        );
        /* array : result must be false */
        $isNotIntValue = array(
            "3.1",
            "a1",
            "3.000001",
            "",
        );
        foreach ($isIntValue as $value) {
            $this->assertEquals(1, exprmgr_int($value), "{$value} is not an integer with exprmgr_int");
        }
        foreach ($isNotIntValue as $value) {
            $this->assertEquals(0, exprmgr_int($value), "{$value} is not an integer with exprmgr_int");
        }
    }

    /**
     * exprmgr_convert_value maps a value using from/to lists. An exact match must
     * return the mapped value in strict mode, including for decimal scale points:
     * abs() then yields a float 0.0, and in PHP `0.0 === 0` is false, which used to
     * make strict mode return null for exact decimal matches.
     *
     * @group em
     */
    public function testConvertValueFunction()
    {
        // Exact decimal match in strict mode must return the mapped value
        // (regression: previously returned null because abs() produced float 0.0).
        $this->assertEquals(
            "20",
            exprmgr_convert_value(2.5, 1, "1.5,2.5,3.5", "10,20,30"),
            "convert_value should map an exact decimal match in strict mode"
        );
        // Exact integer match in strict mode (was already working).
        $this->assertEquals(
            "20",
            exprmgr_convert_value(2, 1, "1,2,3", "10,20,30"),
            "convert_value should map an exact integer match in strict mode"
        );
        // No exact match in strict mode returns null.
        $this->assertNull(
            exprmgr_convert_value(2.4, 1, "1.5,2.5,3.5", "10,20,30"),
            "convert_value strict mode returns null when there is no exact match"
        );
    }
}
