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
        $this->assertFalse($em->GetResult(), 'A number is never less than a non-numeric string');
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
        $this->assertTrue($em->GetResult(), '3 < "30000" should compare numerically as true');
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
        $this->assertTrue($em->GetResult(), '3 < abs(-5) should be true');
        $this->assertCount(0, $em->GetWarnings());
    }

    /**
     * The warning about + must tell apart an addition, a string concatenation
     * and operands with a value not known yet.
     * @see https://bugs.limesurvey.org/view.php?id=15802
     * @dataProvider plusOperatorWarningProvider
     * @param string $expression
     * @param string $expectedMessage
     */
    public function testPlusOperatorWarningMatchesOperandType($expression, $expectedMessage)
    {
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        $lem = \LimeExpressionManager::singleton();
        $knownVars = new \ReflectionProperty($lem, 'knownVars');
        $knownVars->setAccessible(true);
        $savedKnownVars = $knownVars->getValue($lem);
        // Unanswered question, like when checking the survey logic.
        $lem->setKnownVars([
            'Q1' => [
                'code' => '',
                'jsName' => 'java563168X136X5376',
                'jsName_on' => 'java563168X136X5376',
                'readWrite' => 'Y',
                'type' => 'N',
            ],
        ]);
        try {
            $em = new \ExpressionManager();
            $res = $em->RDP_Evaluate($expression);
        } finally {
            $knownVars->setValue($lem, $savedKnownVars);
        }
        $this->assertTrue($res, implode('; ', $em->GetErrors()));
        $warnings = $em->GetWarnings();
        $this->assertCount(1, $warnings);
        $this->assertSame($expectedMessage, $warnings[0]->getMessage());
    }

    /**
     * @return array
     */
    public function plusOperatorWarningProvider()
    {
        $numeric = 'Usage of + with numeric value, see manual about usage of sum.';
        $string = 'Usage of + with string value, see manual about usage of join.';
        $unknown = 'Usage of + with string or numeric value, see manual about usage of sum and join.';
        return [
            'two numbers' => ['1 + 2', $numeric],
            'two string literals' => ['"Here we must use " + "join"', $string],
            'string literal and string function' => ['"Today is " + date("d/m/Y")', $string],
            'string literal and number' => ['"Total: " + 3', $string],
            'unanswered question and number' => ['Q1.NAOK + 1', $unknown],
        ];
    }
}
