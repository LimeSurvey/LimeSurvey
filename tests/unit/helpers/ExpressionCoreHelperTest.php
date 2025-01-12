<?php

namespace ls\tests;

/**
 * Test expression evaluation in PHP vs JS.
 *
 * @see https://bugs.gitit-tech.com/view.php?id=12613
 * @since 2017-06-16
 * @group em
 */
class ExpressionManagerCoreTest extends TestBaseClass
{

    /**
     * List of expressions to test.
     * @var array [[string $value, string $expression], ...]
     */
    public $expressions = [
        ['', '((563168X136X5376.NAOK == " "))'],
        ['0', '((563168X136X5376.NAOK == " "))'],
        ['0', '((563168X136X5376.NAOK == ""))'],
        //[0, '((563168X136X5376.NAOK == " "))'],
        //[0, '((563168X136X5376.NAOK == ""))'],
        ['', '((563168X136X5376.NAOK != " "))'],
        ['3', '((563168X136X5376.NAOK != " "))'],
        ['3', '((563168X136X5376.NAOK != ""))'],
        ['', '((563168X136X5376.NAOK != ""))'],
        ['', '((563168X136X5376.NAOK < " "))'],
        ['3', '((563168X136X5376.NAOK < " "))'],
        ['3', '((563168X136X5376.NAOK < "A"))'],
        [ '3', '((563168X136X5376.NAOK <= " "))'],
        [ '3', '((563168X136X5376.NAOK <= ""))'],
        [ '', '((563168X136X5376.NAOK <= " "))'],
        [ '', '((563168X136X5376.NAOK > " "))'],
        [ '3', '((563168X136X5376.NAOK > " "))'],
        [ ' ', '((563168X136X5376.NAOK > " "))'],
        [ '', '((563168X136X5376.NAOK >= ""))'],
        [ '', '((563168X136X5376.NAOK >= " "))'],
        [ '3', '((563168X136X5376.NAOK >= " "))']
        //[3, '((563168X136X5376.NAOK + "2"))'],
        //[3, '((563168X136X5376.NAOK + 2))']
    ];

    /**
     *
     */
    public static function setUpBeforeClass(): void
    {
        // Check that node is installed.
        $output = [];
        exec('which node ', $output);
        if (empty($output[0])) {
            echo ('Node is not installed');
            exit(7);
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

    /**
     * @group me
     */
    public function testNumericalQuestion()
    {
        $this->markTestSkipped('This test is not finished');
        // TODO: Lack import survey part here
        $sgqa = '563168X136X5376';
        foreach ($this->expressions as $expr) {
            $questionType = 'N';
            $test = new ExpressionCoreAux($expr[1], $sgqa, $questionType, $expr[0]);
            $test->onlynum = 1;
            $test->jsonEncodeEmResult = true;
            $test->compareExpression();
        }
    }

    /**
     * @group me2
     */
    public function testShortTextQuestion()
    {
        $this->markTestSkipped('This test is not finished');
        // TODO: Lack import survey part here
        $sgqa = '563168X136X5376';
        foreach ($this->expressions as $expr) {
            $questionType = 'S';
            $test = new ExpressionCoreAux($expr[1], $sgqa, $questionType, $expr[0]);
            $test->jsonEncodeEmResult = true;
            $test->compareExpression();
        }
    }
}
