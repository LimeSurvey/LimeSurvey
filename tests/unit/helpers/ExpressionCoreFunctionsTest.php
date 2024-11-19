<?php

namespace ls\tests;

/**
 * Test Expression manager PHP function
 *
 * @since 2022-09-30
 * @group em
 */
class ExpressionCoreFunctionTest extends TestBaseClass
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
}
