<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * Test expression evaluation in PHP vs JS.
 * @since 2017-06-16
 * @group em
 */
class ExpressionManagerCoreTest extends TestBaseClass
{

    /**
     *
     */
    public static function setUpBeforeClass()
    {
        // Check that node is installed.
        $output = [];
        exec('which node ', $output);
        if (empty($output[0])) {
            die('Node is not installed');
        }

        require_once(__DIR__ . '/ExpressionCoreAux.php');
    }

    /**
     * Some code on how to use tokens manually.
     */
    public function notes()
    {
        /*
        $number = [
            0 => '3',
            1 => 2,
            2 => 'NUMBER'
        ];

        $dqString = [
            0 => ' ',
            1 => 26,
            2 => 'DQ_STRING'
        ];

        $em->RDP_StackPush($number);
        $em->RDP_StackPush($dqString);

        $compare = [
            0 => '>=',
            1 => 23,
            2 => 'COMPARE'
        ];
        $noErrors = $em->RDP_EvaluateBinary($compare);
        $this->assertTrue($noErrors);

        $result = $em->RDP_StackPop();

        $em->RDP_StackPush($number);
        $em->RDP_StackPush($dqString);
        $em->RDP_StackPush($compare);
        $em->SetJsVarsUsed([]);
         */

    }

    /**
     * @group me
     */
    public function testAllExpressions()
    {
        $sgqa = '563168X136X5376';
        $expressions = [
            // Value and expression.
            ['', '((563168X136X5376.NAOK == " "))']
        ];
        foreach ($expressions as $expr) {
            $test = new ExpressionCoreAux($expr[1], $sgqa, $expr[0]);
            $test->onlynum = 1;
            $test->jsonEncodeEmResult = true;
            $test->compareExpression();
        }
    }

    /**
     * Expression: '' == ' '
     */
    public function testCompareEmptyEqSpace()
    {
    }

    /**
     * Expression: '0' == ' '
     */
    public function testCompareZeroEqSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK == " "))';
        $value = '0';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '' != ' '
     */
    public function testCompareEmptyNeSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK != " "))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '3' != ' '
     */
    public function testCompareNumberNeSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK != " "))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '3' != ''
     */
    public function testCompareNumberNeEmpty()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK != ""))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '' != ''
     */
    public function testCompareEmptyNeEmpty()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK != ""))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '' < ' '
     */
    public function testCompareEmptyLtSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK < " "))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '3' < ' '
     */
    public function testCompareNumberLtSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK < " "))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '3' < 'A'
     */
    public function testCompareNumberLtLetter()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK < "A"))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * '3' <= ' '
     */
    public function testCompareNumberLeSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK <= " "))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * '3' <= ''
     */
    public function testCompareNumberLeEmpty()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK <= ""))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * '' <= ' '
     */
    public function testCompareEmptyLeSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK <= " "))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '' > ' '
     */
    public function testCompareEmptyGtSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK > " "))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '3' > ' '
     */
    public function testCompareNumberGtSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK > " "))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: ' ' > ' '
     */
    public function testCompareSpaceGtSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK > " "))';
        $value = ' ';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: '' >= ''
     */
    public function testCompareEmptyGeEmpty()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK >= ""))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }


    /**
     * Expression: '' >= ' '
     */
    public function testCompareEmptyGeSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK >= " "))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * When constructing condition, empty string is represented
     * as "No answer".
     * Expression: '3' >= ' '
     */
    public function testCompareNumberGeSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK >= " "))';
        $value = '3';
        $this->compareExpression($sgqa, $value, $expression);
    }

    /**
     * Expression: 3 + '2'
     */
    public function testCompareNumberPlusString()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK + "2"))';
        $value = 3;
        $jsonEncodeResult = false;
        $this->compareExpression($sgqa, $value, $expression, $jsonEncodeResult);
    }

    /**
     * Expression: 3 + 2
     */
    public function testCompareNumberPlusNumber()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK + 2))';
        $value = 3;
        $jsonEncodeResult = true;
        $this->compareExpression($sgqa, $value, $expression, $jsonEncodeResult);
    }

    /**
     * 
     */
    public function testGeneratedJavascript()
    {
        /*
        $pageInfo = [
            'qid' => '5377',
            'gseq' => 0,
            'eqn' => '((563168X136X5376.NAOK >= \" \"))',
            'result' => false,
            'numJsVars' => 1,
            'relevancejs' => '(((LEMval(\'563168X136X5376.NAOK\')  >= \" \")))',
            'relevanceVars' => 'java563168X136X5376',
            'jsResultVar' => 'java563168X136X5377',
            'type' => 'N',
            'hidden' => false,
            'hasErrors' => false
        ];
         */
    }
}
