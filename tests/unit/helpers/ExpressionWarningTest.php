<?php

namespace ls\tests;

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
}
