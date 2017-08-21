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
     * When constructing condition, empty string is represented
     * as "No answer".
     */
    public function testCompareNumberAndEmptyString()
    {
        $em = new \ExpressionManager();
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

        $limeEm = \LimeExpressionManager::singleton();
        $limeEm->setKnownVars(
            [
                '563168X136X5376' => [
                ]
            ]
        );

        $em->RDP_Evaluate('((563168X136X5376.NAOK >= " "))');
        $result = $em->GetResult();
        echo '<pre>'; var_dump($result); echo '</pre>';
        $errors = $em->RDP_GetErrors();
        echo '<pre>'; var_dump($errors); echo '</pre>';
        $js = $em->GetJavaScriptEquivalentOfExpression();

        $js = str_replace('"', "'", $js);
        echo $js;

        $output = [];
        exec(
            sprintf(
                'node -p "%s"',
                $js
            ),
            $output
        );
        echo '<pre>'; var_dump($output); echo '</pre>';

    }

    /**
     * 
     */
    public function testGeneratedJavascript()
    {
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
    }
}
