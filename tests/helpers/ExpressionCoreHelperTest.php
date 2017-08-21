<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * @since 2017-06-16
 * @group em
 */
class ExpressionManagerCoreTest extends TestBaseClass
{

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
     * When constructing condition, empty string is represented
     * as "No answer".
     */
    public function testCompareNumberAndEmptyString()
    {
        $sgqa = '563168X136X5376';

        // Input value 3.
        $_SESSION['survey_563168'][$sgqa] = '3';

        $em = new \ExpressionManager();
        $limeEm = \LimeExpressionManager::singleton();
        $limeEm->setVariableAndTokenMappingsForExpressionManager('563168');
        $limeEm->setKnownVars(
            [
                $sgqa => [
                    'sgqa' => $sgqa,
                    'type' => 'N'
                ]
            ]
        );

        $expression = '((563168X136X5376.NAOK >= " "))';

        $em->RDP_Evaluate($expression);

        $result = $em->GetResult();

        $errors = $em->RDP_GetErrors();
        $js = $em->GetJavaScriptEquivalentOfExpression();

        $nodeOutput = $this->runNode($js);

        $this->assertCount(1, $nodeOutput);
        $this->assertEquals(json_encode($result), $nodeOutput[0], 'JS and PHP must return same result');
    }

    /**
     * Run $js code in Node on command line.
     * @param string $js
     * @return array
     */
    protected function runNode($js)
    {
        // Only use single quotes.
        $js = str_replace('"', "'", $js);
        $output = [];
        exec(
            sprintf(
                'node -p "%s"',
                $js
            ),
            $output
        );
        return $output;
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
