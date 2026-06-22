<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test expression evaluation in PHP vs JS.
 *
 * @see https://bugs.limesurvey.org/view.php?id=12613
 * @since 2017-06-16
 * @group em
 */
class ExpressionManagerCoreTest extends TestBaseClass
{
    /**
     * Expression evaluation test cases.
     * Each entry: [variable value, expression, expected boolean result]
     * The SGQA variable 563168X136X5376 is registered with the given value before evaluation.
     * Note: many "false" results come from the EM's type-mismatch rules (numeric vs string).
     * @var array
     */
    public function expressionProvider(): array
    {
        return [
            'empty == space'       => ['', '((563168X136X5376.NAOK == " "))', false],
            'zero == space'        => ['0', '((563168X136X5376.NAOK == " "))', false],
            'zero == empty'        => ['0', '((563168X136X5376.NAOK == ""))', false],
            'empty != space'       => ['', '((563168X136X5376.NAOK != " "))', true],
            'three != space'       => ['3', '((563168X136X5376.NAOK != " "))', true],
            'three != empty'       => ['3', '((563168X136X5376.NAOK != ""))', true],
            'empty != empty'       => ['', '((563168X136X5376.NAOK != ""))', false],
            'empty < space'        => ['', '((563168X136X5376.NAOK < " "))', true],
            'three < space'        => ['3', '((563168X136X5376.NAOK < " "))', false],
            'three < A'            => ['3', '((563168X136X5376.NAOK < "A"))', false],
            'three <= space'       => ['3', '((563168X136X5376.NAOK <= " "))', false],
            'three <= empty'       => ['3', '((563168X136X5376.NAOK <= ""))', false],
            'empty <= space'       => ['', '((563168X136X5376.NAOK <= " "))', true],
            'empty > space'        => ['', '((563168X136X5376.NAOK > " "))', false],
            'three > space'        => ['3', '((563168X136X5376.NAOK > " "))', false],
            'space > space'        => [' ', '((563168X136X5376.NAOK > " "))', false],
            'empty >= empty'       => ['', '((563168X136X5376.NAOK >= ""))', true],
            'empty >= space'       => ['', '((563168X136X5376.NAOK >= " "))', false],
            'three >= space'       => ['3', '((563168X136X5376.NAOK >= " "))', false],
        ];
    }

    public function testBasicEvaluation(): void
    {
        // Verify the core evaluator works with a simple arithmetic expression.
        $em = new \ExpressionManager();
        $result = $em->RDP_Evaluate('1 + 2');
        $this->assertTrue($result, 'Expression "1 + 2" should evaluate without errors');
        $this->assertEquals(3, $em->GetResult());
    }

    /**
     * @dataProvider expressionProvider
     */
    public function testExpressionEvaluation(string $varValue, string $expression, bool $expected): void
    {
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        \LimeExpressionManager::setValueToKnowVar('563168X136X5376', $varValue);

        $em = new \ExpressionManager();
        $success = $em->RDP_Evaluate($expression);
        $this->assertTrue($success, "Expression should evaluate without errors: $expression (errors: " . implode('; ', $em->GetErrors()) . ")");
        $this->assertSame($expected, $em->GetResult(), "With var='$varValue', expression: $expression");
    }

    public function testKnownNonEmPlaceholdersDoNotRaiseErrors(): void
    {
        $em = new \ExpressionManager();
        $result = $em->sProcessStringContainingExpressions(
            'I accept the {STARTPOLICYLINK}privacy policy{ENDPOLICYLINK}.'
        );

        $this->assertSame('I accept the {STARTPOLICYLINK}privacy policy{ENDPOLICYLINK}.', $result);
        $this->assertFalse($em->HasErrors());

        $prettyPrint = $em->GetLastPrettyPrintExpression();
        $this->assertStringContainsString('STARTPOLICYLINK', $prettyPrint);
        $this->assertStringContainsString('ENDPOLICYLINK', $prettyPrint);
        $this->assertStringNotContainsString('em-error', $prettyPrint);
        $this->assertStringNotContainsString('em-var-error', $prettyPrint);
    }
}
