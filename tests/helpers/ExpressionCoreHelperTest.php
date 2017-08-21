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
     * 
     */
    public function testCompareNumberAndEmptyString()
    {
        $em = new \ExpressionManager();

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
        $result = $em->RDP_EvaluateBinary($compare);
        $this->asserTrue($result);
    }
}
