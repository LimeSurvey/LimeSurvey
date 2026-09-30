<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test the not applicable (NA) check of the JavaScript generated for an expression.
 * In text replacement and equation, variables used directly as argument of count, sum or unique don't need NAOK.
 *
 * @see https://bugs.limesurvey.org/view.php?id=14932
 * @group em
 */
class ExpressionManagerNATolerantTest extends TestBaseClass
{
    /**
     * Set up two known variables available in the page.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        $lem = \LimeExpressionManager::singleton();
        $lem->setKnownVars(
            [
                '563168X136X5376' => [
                    'sgqa' => '563168X136X5376',
                    'type' => 'N',
                    'jsName' => 'java563168X136X5376',
                ],
                '563168X136X5377' => [
                    'sgqa' => '563168X136X5377',
                    'type' => 'N',
                    'jsName' => 'java563168X136X5377',
                ],
            ]
        );
    }

    /**
     * Expression and the variables expected in the LEManyNA check, in default mode and in NA tolerant mode.
     * @return array
     */
    public function expressionProvider(): array
    {
        $a = '563168X136X5376';
        $b = '563168X136X5377';
        return [
            'count' => ["count($a, $b)", [$a, $b], []],
            'sum' => ["sum($a, $b)", [$a, $b], []],
            'unique' => ["unique($a, $b)", [$a, $b], []],
            'count with NAOK' => ["count($a.NAOK, $b)", [$b], []],
            'variable also used outside count' => ["count($a, $b) + $a", [$a, $b], [$a]],
            'part of an argument' => ["count($a + 1, $b)", [$a, $b], [$a]],
            'argument inside bracket' => ["sum($a, ($b))", [$a, $b], [$b]],
            'argument of an inner function' => ["count(if($a == 1, $b, 1))", [$a, $b], [$a, $b]],
            'countif is not NA tolerant' => ["countif(1, $a, $b)", [$a, $b], [$a, $b]],
            'no function' => ["$a + $b", [$a, $b], [$a, $b]],
        ];
    }

    /**
     * @dataProvider expressionProvider
     * @param string $expression
     * @param string[] $expectedDefault variables checked in default mode (boolean expressions)
     * @param string[] $expectedNATolerant variables checked in NA tolerant mode (text replacement and equation)
     * @return void
     */
    public function testNACheckedVariables(string $expression, array $expectedDefault, array $expectedNATolerant): void
    {
        $em = new \ExpressionManager();
        $this->assertTrue($em->RDP_Evaluate($expression), print_r($em->RDP_GetErrors(), true));
        $this->assertSame($expectedDefault, $this->getNACheckedVariables($em->GetJavaScriptEquivalentOfExpression()));
        $this->assertSame($expectedNATolerant, $this->getNACheckedVariables($em->GetJavaScriptEquivalentOfExpression(true)));
        /* Cache of one mode must not be used for the other one */
        $this->assertSame($expectedDefault, $this->getNACheckedVariables($em->GetJavaScriptEquivalentOfExpression()));
    }

    /**
     * Text replacement use the NA tolerant mode.
     * @return void
     */
    public function testReplacementIsNATolerant(): void
    {
        $em = new \ExpressionManager();
        $this->assertTrue($em->RDP_Evaluate('count(563168X136X5376, 563168X136X5377)'));
        $js = $em->GetJavaScriptFunctionForReplacement(0, 'LEMtailor_Q_0_1', '');
        $this->assertStringNotContainsString('LEManyNA', $js);
        $this->assertStringContainsString("LEMcount(LEMval('563168X136X5376') , LEMval('563168X136X5377') )", $js);
    }

    /**
     * Get the variables checked by LEManyNA in a generated JavaScript expression.
     * @param string $js
     * @return string[]
     */
    private function getNACheckedVariables(string $js): array
    {
        if (!preg_match("/^LEMif\(LEManyNA\('(.*?)'\),''/", $js, $matches)) {
            return [];
        }
        return explode("', '", $matches[1]);
    }
}
