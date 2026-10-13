<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Date values set by URL prefill, default value or user input must be valid before being saved in the response table.
 * See mantis #16937
 */
class PrefillDateValidityTest extends TestBaseClass
{
    /**
     * Call the private LimeExpressionManager::isValidDateTimeValue
     *
     * @param string $value the value to check
     * @return boolean
     */
    private function isValidDateTimeValue($value)
    {
        $method = new \ReflectionMethod(\LimeExpressionManager::class, 'isValidDateTimeValue');
        $method->setAccessible(true);
        return $method->invoke(null, $value);
    }

    /**
     * Values the database can store are accepted
     */
    public function testValidDates()
    {
        $this->assertTrue($this->isValidDateTimeValue('2020-12-31'));
        $this->assertTrue($this->isValidDateTimeValue('2020-02-29 23:59'));
        $this->assertTrue($this->isValidDateTimeValue('1970-01-01 10:30:00'));
    }

    /**
     * Unparsable or out of range values are rejected
     */
    public function testInvalidDates()
    {
        $this->assertFalse($this->isValidDateTimeValue('10007-06-07 00:00'));
        $this->assertFalse($this->isValidDateTimeValue('2020-13-01'));
        $this->assertFalse($this->isValidDateTimeValue('2021-02-29'));
        $this->assertFalse($this->isValidDateTimeValue('2020-01-01 24:00'));
        $this->assertFalse($this->isValidDateTimeValue('2020-01-01 10:60'));
        $this->assertFalse($this->isValidDateTimeValue('31/12/2020'));
        $this->assertFalse($this->isValidDateTimeValue('ABCDEFGHIJ'));
    }
}
