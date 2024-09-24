<?php

namespace ls\tests;

use Yii;
use ExpressionManager;

class ExpressionManagerTest extends TestBaseClass
{
    /**
     *
     * @var ExpressionManager
     */
    protected $em;

    public function setUp(): void
    {
        parent::setUp();
        Yii::import('application.helpers.expressions.em_core_helper', 'true');
        if (!function_exists('gT')) {
            // Create gT function that ExpressionManager uses (but ideally should not).
            eval('function gT() { }');
        }

        $this->em = new ExpressionManager();
    }

    public function testEvaluator()
    {
        $booleanExpressions = array(
            "1" => true,
            "0" => false,
            "" => false,
            "1 == 1" => true,
            "0 == 1" => false,
            "1 && 0" => false,
            "1 && 1" => true,
            "1 || 0" => true,
            "0 || 0" => false,
        );

        foreach ($booleanExpressions as $expr => $expected) {
            $this->assertEquals($expected, $this->em->ProcessBooleanExpression($expr), "Expression: '$expr'");
        }
    }

    public function testFunctions()
    {
        $functions = array(
            'abs(5)' => 5,
            'abs(-5)' => 5,
            'abs(0)' => 0,
            'abs(6.5)' => 6.5,
            'abs(-8.0)' => 8,
            'abs("-7.3")' => 7.3,
            'abs("3")' => 3,
            'abs("-90")' => 90,
            'abs("string")' => false
        );

        foreach ($functions as $function => $expected) {
            $this->assertEquals($expected, $this->em->sProcessStringContainingExpressions('{' . $function . '}'));
        }

        /**
         * These functions must be evaluated differently since they return
         * a float.
         *
         * See: https://docs.phpunit.de/en/8.5/assertions.html#assertequalswithdelta
         *
         * Other option is to casto to string the actual value.
         */
        $deltaFunctions = array(
            'acos(0.5)' => acos(0.5),
            'acos(0.1)' => acos(0.1),
        );

        foreach ($deltaFunctions as $function => $expected) {
            $this->assertEqualsWithDelta($expected, $this->em->sProcessStringContainingExpressions('{' . $function . '}'), 0.0000000001);
        }
    }

    public function testEscapes()
    {
        $strings = array(
            '\{1+1}' => '{1+1}',
            'x{1+1}' => 'x2',
            'x{1+1\}' => 'x{1+1}',
        );
        foreach ($strings as $escaped => $expected) {
            $this->assertEquals($expected, $this->em->sProcessStringContainingExpressions($escaped));
        }
    }

    public function testJuggling()
    {
        // Original test array.
        /*$equalities = array(
            '"1" == 1' => 1,
            '"5" + "2"' => 52,
            '"1" == 0' => '', // False is an empty string.
            '1 == "1"' => 1,
            '1 + "2"' => 12,
            '"1" + "a"' => '1a',
            '1 + "a"' => '1a',
        );*/

        $equalities = array(
            '"1" == 1' => 1,
            '"5" + "2"' => 52, // String concatenation.
            '"1" == 0' => '', // False is an empty string.
            '1 == "1"' => 1,
            '1 + "2"' => 12,
            '"1" + "a"' => '1a',
            '1 + "a"' => '1a',
            '"05" + "1"' => "051", // String concatenation.
            '"" + "1" + "2"' => 12
        );

        foreach ($equalities as $expression => $expected) {
            $result = $this->em->sProcessStringContainingExpressions('{' . $expression . '}');
            $this->assertEquals($expected, $result);
        }
    }
}
