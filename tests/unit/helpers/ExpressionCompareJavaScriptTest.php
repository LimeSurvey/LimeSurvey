<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Comparisons must give the same result in the browser (JavaScript) as on the server (PHP),
 * else a question is shown but its answer is not saved.
 *
 * @see https://bugs.limesurvey.org/view.php?id=19807
 * @group em
 */
class ExpressionCompareJavaScriptTest extends TestBaseClass
{
    /**
     * Answers of the question Q1 used in the expressions
     * @var string[]
     */
    private $values = ['', '0', '3', '-2', '10', '1.5', '007', 'A1', 'a', ' '];

    /**
     * @var string[]
     */
    private $expressions = [
        'Q1.NAOK > ""',
        'Q1.NAOK >= ""',
        'Q1.NAOK < ""',
        'Q1.NAOK <= ""',
        'Q1.NAOK == ""',
        'Q1.NAOK != ""',
        'Q1.NAOK > " "',
        'Q1.NAOK < " "',
        'Q1.NAOK > 0',
        'Q1.NAOK >= 0',
        'Q1.NAOK < 0',
        'Q1.NAOK <= 0',
        'Q1.NAOK == 0',
        'Q1.NAOK != 0',
        'Q1.NAOK gt 2',
        'Q1.NAOK le 2',
        'Q1.NAOK eq "A1"',
        'Q1.NAOK ne "A1"',
        'Q1.NAOK > "2"',
        'Q1.NAOK < ("2")',
        'Q1.NAOK < "A"',
        'Q1.NAOK > 2 == 1',
        '1 == Q1.NAOK > 2',
        '0 < Q1.NAOK < 5',
        '(Q1.NAOK > 2) and (Q1.NAOK < 5)',
        '!Q1.NAOK > 0',
        'if(Q1.NAOK > 2, "big", "small")',
        'sum(Q1.NAOK, 1) > 2',
        'is_empty(Q1.NAOK) == 0',
        'Q1.NAOK + "" < 20 + ""',
        '(Q1.NAOK + "") > "2" + ""',
        '"" + Q1.NAOK + 1 > 2',
        'Q1.NAOK * 2 + 1 > 2',
        '5 + 2 * -1 > Q1.NAOK',
        'staticlen("ab") > 1 and Q1.NAOK > 2',
        'Q1.NAOK > staticlen("abc" > "a")',
    ];

    /**
     * @return void
     */
    public function testComparisonsMatchPhp(): void
    {
        \Yii::import('application.helpers.expressions.em_manager_helper', true);
        $lem = \LimeExpressionManager::singleton();
        $knownVars = new \ReflectionProperty($lem, 'knownVars');
        $knownVars->setAccessible(true);
        $savedKnownVars = $knownVars->getValue($lem);
        $cases = [];
        try {
            foreach ($this->values as $value) {
                $lem->setKnownVars([
                    'Q1' => [
                        'code' => $value,
                        'jsName' => 'java563168X136X5376',
                        'jsName_on' => 'java563168X136X5376',
                        'readWrite' => 'Y',
                        'type' => 'L',
                    ],
                ]);
                foreach ($this->expressions as $expression) {
                    $em = new \ExpressionManager();
                    // A function without JavaScript is evaluated on the server and put into the JavaScript as a value
                    $em->RegisterFunctions(['staticlen' => ['strlen', null, '', 'int staticlen(string)', '', 1]]);
                    $this->assertTrue($em->RDP_Evaluate($expression), "Expression should evaluate without errors: $expression");
                    $cases[] = [
                        'value' => $value,
                        'expression' => $expression,
                        'php' => $em->GetResult(),
                        'js' => $em->GetJavaScriptEquivalentOfExpression(),
                    ];
                }
            }
        } finally {
            $knownVars->setValue($lem, $savedKnownVars);
        }

        $jsResults = $this->runJavaScript($cases);

        $differences = [];
        foreach ($cases as $i => $case) {
            if ($jsResults[$i] !== $case['php']) {
                $differences[] = sprintf(
                    'Q1 = %s: %s is %s in PHP, %s in JavaScript (%s)',
                    json_encode($case['value']),
                    $case['expression'],
                    json_encode($case['php']),
                    json_encode($jsResults[$i]),
                    $case['js']
                );
            }
        }
        $this->assertSame([], $differences);
    }

    /**
     * Runs the JavaScript of each case with the em_javascript.js functions in Node.js
     *
     * @param array[] $cases
     * @return array The result of each case
     */
    private function runJavaScript(array $cases)
    {
        $rootDir = \Yii::app()->getConfig('rootdir');
        $script = "const fs = require('fs');\n"
            . "Decimal = require(" . json_encode($rootDir . '/node_modules/decimal.js/decimal.js') . ");\n"
            . "$ = function() { return { on: function() {} }; };\n"
            . "var currentValue = '';\n"
            . "document = { getElementById: function(id) { return { value: currentValue }; } };\n"
            . "eval(fs.readFileSync(" . json_encode($rootDir . '/assets/packages/expressions/em_javascript.js') . ", 'utf8'));\n"
            . "LEMradix = '.';\n"
            . "LEMmode = 'survey';\n"
            . "LEMalias2varName = { 'Q1': 'java563168X136X5376' };\n"
            . "LEMvarNameAttr = { 'java563168X136X5376': { jsName: 'java563168X136X5376', sgqa: '563168X136X5376', qid: '', type: 'L', gseq: 0 } };\n"
            . "var results = [];\n";
        foreach ($cases as $case) {
            $script .= "currentValue = " . json_encode($case['value']) . "; results.push(" . $case['js'] . ");\n";
        }
        $script .= "console.log(JSON.stringify(results));\n";
        $file = tempnam(sys_get_temp_dir(), 'emcompare') . '.js';
        file_put_contents($file, $script);
        try {
            exec('node ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);
        } finally {
            unlink($file);
        }
        $this->assertSame(0, $exitCode, implode("\n", $output));
        return json_decode(implode('', $output), true);
    }
}
