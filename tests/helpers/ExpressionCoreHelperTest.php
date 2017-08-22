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
        exec('node -v', $output);
        if (strpos($output[0], 'command not found') !== false) {
            die('Node is not installed');
        }
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
     * Expression: '' == ' '
     */
    public function testCompareEmptyEqSpace()
    {
        $sgqa = '563168X136X5376';
        $expression = '((563168X136X5376.NAOK == " "))';
        $value = '';
        $this->compareExpression($sgqa, $value, $expression);
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
     * @param string $sgqa
     * @param string $expression
     * @param boolean $jsonEncode If true, run json_encode on PHP eval result. Good for when node returns boolean.
     * @return void
     */
    protected function compareExpression($sgqa, $value, $expression, $jsonEncode = true)
    {
        // Input value 3.
        $_SESSION['survey_563168'][$sgqa] = $value;

        $em = new \ExpressionManager();
        $lem = \LimeExpressionManager::singleton();
        $lem->setVariableAndTokenMappingsForExpressionManager('563168');
        $lem->setKnownVars(
            [
                $sgqa => [
                    'sgqa' => $sgqa,
                    'type' => 'N',
                    'jsName' => 'java' . $sgqa // This will trigger LEMval()
                ]
            ]
        );

        $em->RDP_Evaluate($expression);

        $result = $em->GetResult();

        if ($jsonEncode) {
            $result = json_encode($result);
        }

        $errors = $em->RDP_GetErrors();
        $this->assertEmpty($errors);
        $js = $em->GetJavaScriptEquivalentOfExpression();

        $js = $this->getDummyNodeSetup($sgqa, $value, null, 0) . $js;

        $nodeOutput = $this->runNode($js);

        $this->assertCount(1, $nodeOutput);
        $this->assertEquals($result, $nodeOutput[0], 'JS and PHP must return same result');
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
        $command = sprintf(
            'node -p "%s"',
            $js
        );
        exec($command, $output);
        return $output;
    }

    /**
     * JS code to setup environment so LEMval() can run.
     * @param string $sgqa
     * @param mixed $value
     * @param string $alias
     * @param int $onlynum
     * @return string
     */
    public function getDummyNodeSetup($sgqa, $value, $alias, $onlynum = 0)
    {
        if (is_string($value)) {
            $value = "'$value'";
        }
        list($surveyId, $groupId, $questionid) = explode('X', $sgqa, 3);
        return <<<EOT
            // Dummy jQuery.
            $ = function() {
                return {
                    on: function() {}
                }
            };
            // Dummy document.
            document = {
                getElementById: function(id) {
                    //console.log(id);
                    if (id == 'relevance$surveyId' || id == 'relevance' || id == 'relevanceG0') {
                        return {value: 1};
                    }
                    return {value: $value};
                }
            }
            eval(fs.readFileSync('./scripts/expressions/em_javascript.js', {encoding: 'utf8'}));
            LEMradix = ',';
            LEMmode = 'survey';
            LEMalias2varName = {
                '$alias': 'java$sgqa',
                '$sgqa': 'java$sgqa'
            };
            LEMvarNameAttr = {
                'java$sgqa': {
                    'jsName':'java$sgqa',
                    'jsName_on':'java$sgqa',
                    'sgqa':'$sgqa',
                    'qid': '$surveyId',
                    'gid': '$groupId',
                    'type':'N',
                    'default':'',
                    'rowdivid':'',
                    'onlynum': $onlynum,
                    'gseq':0,
                    'answers': {
                        'Y':'Ja',
                        'N':'Nei'
                    }
                },
            };
EOT;
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
