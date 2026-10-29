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
     * A non-breaking space (as inserted by the HTML editor) is whitespace between tokens.
     *
     * @see https://bugs.limesurvey.org/view.php?id=18493
     * @return void
     */
    public function testNonBreakingSpaceIsWhitespace(): void
    {
        $em = new \ExpressionManager();
        $result = $em->RDP_Evaluate("1\u{00A0}OR 0");
        $this->assertTrue($result, 'Expression should evaluate without errors (errors: ' . implode('; ', array_column($em->GetErrors(), 0)) . ')');
        $this->assertTrue($em->GetResult());

        $em = new \ExpressionManager();
        $result = $em->RDP_Evaluate("1\u{00A0}\u{00A0}+ 2");
        $this->assertTrue($result, 'Expression should evaluate without errors (errors: ' . implode('; ', array_column($em->GetErrors(), 0)) . ')');
        $this->assertEquals(3, $em->GetResult());
    }

    /**
     * Non-breaking spaces inside a quoted string are kept.
     *
     * @return void
     */
    public function testNonBreakingSpaceInStringIsKept(): void
    {
        $em = new \ExpressionManager();
        $result = $em->RDP_Evaluate("\"a\u{00A0}b\"");
        $this->assertTrue($result);
        $this->assertSame("a\u{00A0}b", $em->GetResult());
    }

    /**
     * Expressions with an on-page question variable, and how often the generated JavaScript
     * reads it as entered (LEMval with keepText) and converted to a number (plain LEMval).
     * @return array
     */
    public function regexMatchJavaScriptProvider(): array
    {
        return [
            'regexMatch argument'              => ['regexMatch("/^\\+[0-9]+$/", Q1.NAOK)', 1, 0],
            'regex validation equation'        => ['(if(is_empty(Q1.NAOK),0,!regexMatch("/^\\+[0-9]+$/", Q1.NAOK)))', 1, 1],
            'arithmetic inside regexMatch'     => ['regexMatch("/^[0-9]+$/", Q1.NAOK + 1)', 0, 1],
            'other function inside regexMatch' => ['regexMatch("/^[0-9]+$/", sum(Q1.NAOK))', 0, 1],
            'comparison'                       => ['Q1.NAOK > 1', 0, 1],
        ];
    }

    /**
     * A text answer like '+123' must be matched by regexMatch in the browser as entered, as on the server.
     *
     * @see https://bugs.limesurvey.org/view.php?id=19212
     * @dataProvider regexMatchJavaScriptProvider
     * @param string $expression
     * @param int $keepTextCount Expected number of LEMval calls with keepText
     * @param int $convertedCount Expected number of plain LEMval calls
     * @return void
     */
    public function testRegexMatchJavaScriptKeepsTextAnswer(string $expression, int $keepTextCount, int $convertedCount): void
    {
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        $lem = \LimeExpressionManager::singleton();
        $knownVars = new \ReflectionProperty($lem, 'knownVars');
        $knownVars->setAccessible(true);
        $savedKnownVars = $knownVars->getValue($lem);
        $lem->setKnownVars([
            'Q1' => [
                'code' => '+123',
                'jsName' => 'java563168X136X5376',
                'jsName_on' => 'java563168X136X5376',
                'readWrite' => 'Y',
                'type' => 'T',
            ],
        ]);
        try {
            $em = new \ExpressionManager();
            $this->assertTrue($em->RDP_Evaluate($expression), "Expression should evaluate without errors: $expression");
            $js = $em->GetJavaScriptEquivalentOfExpression();
        } finally {
            $knownVars->setValue($lem, $savedKnownVars);
        }
        $this->assertSame($keepTextCount, substr_count($js, "LEMval('Q1.NAOK', true)"), $js);
        $this->assertSame($convertedCount, substr_count($js, "LEMval('Q1.NAOK') "), $js);
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
}
