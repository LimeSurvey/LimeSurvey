<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test expression warning.
 */
class ExpressionWarningTest extends TestBaseClass
{
    /**
     * Test basic functionality.
     */
    public function testBasic()
    {
        $em = new \ExpressionManager();
        $res = $em->RDP_Evaluate('"2" > "18"');
        // "2" is higher than "18" when comparing alphabetically.
        $this->assertTrue($res);
        $warnings = $em->GetWarnings();
        // Exactly one warning about alphabetic compare.
        $this->assertCount(1, $warnings);
    }

    /**
     * Comparing a number with a function that returns a non-numeric string
     * must warn about the type mismatch.
     * @see https://bugs.limesurvey.org/view.php?id=15803
     */
    public function testWarnsOnNumericComparisonWithStringFunctionResult()
    {
        $em = new \ExpressionManager();
        $res = $em->RDP_Evaluate('3 lt str_pad("A", 3)');
        $this->assertTrue($res, implode('; ', $em->GetErrors()));
        $this->assertCount(1, $em->GetWarnings());
    }

    /**
     * Functions whose result is a numeric-looking string (e.g. str_pad() used to
     * zero-pad a number) must not trigger a false-positive type-mismatch warning.
     * @see https://bugs.limesurvey.org/view.php?id=15803
     */
    public function testNoWarningWhenStringFunctionResultIsNumeric()
    {
        $em = new \ExpressionManager();
        $res = $em->RDP_Evaluate('3 lt str_pad(3, 5, "0")');
        $this->assertTrue($res, implode('; ', $em->GetErrors()));
        $this->assertCount(0, $em->GetWarnings());
    }

    /**
     * Functions returning a genuine number must keep comparing numerically,
     * without a spurious type-mismatch warning.
     * @see https://bugs.limesurvey.org/view.php?id=15803
     */
    public function testNoWarningOnNumericFunctionResult()
    {
        $em = new \ExpressionManager();
        $res = $em->RDP_Evaluate('3 lt abs(-5)');
        $this->assertTrue($res, implode('; ', $em->GetErrors()));
        $this->assertCount(0, $em->GetWarnings());
    }
}
