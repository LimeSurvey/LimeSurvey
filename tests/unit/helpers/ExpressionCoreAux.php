<?php

namespace ls\tests;

use PHPUnit\Framework\TestCase;

/**
 * Helper class for expression core tests.
 */
class ExpressionCoreAux extends TestCase
{
    /**
     * If true, compares JS result with json_encode(PHP result)
     * @var boolean
     */
    public $jsonEncodeEmResult = false;

    /**
     * If true, sets onlynum = 1 in LEMvarNameAttr.
     * @var int
     */
    public $onlynum = 0;

    /**
     * @var string
     */
    public $expression;

    /**
     * Survey-group-question-answer code, like '123X123X123_1'.
     * @var string
     */
    public $sgqa;

    /**
     * Question type char. Defaults to 'T' = long free text.
     * @var string
     */
    public $questionType = 'T';

    /**
     * Value of question, as in $_SESSION and <input>.
     * @mixed
     */
    public $value;

    /**
     * Question alias.
     * @var string
     */
    public $alias = 'test';

    /**
     * @param string $expression
     * @param string $sgqa
     * @param string $questionType
     * @param mixed $value
     */
    public function __construct($expression, $sgqa, $questionType, $value)
    {
        $this->expression = $expression;
        $this->sgqa = $sgqa;
        $this->questionType = $questionType;
        $this->value = $value;
    }

    /**
     * @return void
     */
    public function compareExpression()
    {
        // Input value 3.
        $_SESSION['responses_563168'][$this->sgqa] = $this->value;

        $em = new \ExpressionManager();
        $lem = \LimeExpressionManager::singleton();
        $lem->setVariableAndTokenMappingsForExpressionManager('563168');
        $lem->setKnownVars(
            [
                $this->sgqa => [
                    'sgqa' => $this->sgqa,
                    'type' => 'N',
                    'jsName' => 'java' . $this->sgqa // This will trigger LEMval()
                ]
            ]
        );

        $em->RDP_Evaluate($this->expression);

        $emResult = $em->GetResult();

        if ($this->jsonEncodeEmResult) {
            $emResult = json_encode($emResult);
        }

        $errors = $em->RDP_GetErrors();
        $this->assertEmpty($errors, print_r($errors, true));
        $jsOfExpression = $em->GetJavaScriptEquivalentOfExpression();

        $js = $this->getDummyNodeSetup() . $jsOfExpression;

        $nodeOutput = $this->runNode($js);

        $this->assertCount(1, $nodeOutput);
        $this->assertEquals(
            $emResult,
            $nodeOutput[0],
            sprintf(
                'JS (%s) and PHP (%s) must return same result. Expression: %s, value: %s',
                $nodeOutput[0],
                $emResult,
                $this->expression,
                json_encode($this->value)
            )
        );
    }


    /**
     * JS code to setup environment so LEMval() can run.
     * @param string $sgqa
     * @param mixed $value
     * @param string $alias
     * @param int $onlynum
     * @return string
     */
    public function getDummyNodeSetup()
    {
        if (is_string($this->value)) {
            $value = "'{$this->value}'";
        } else {
            $value = $this->value;
        }

        $qid = substr(explode("_", $this->sgqa)[0], 1);
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
                    if (id == 'relevance$qid' || id == 'relevance' || id == 'relevanceG0') {
                        return {value: 1};
                    }
                    return {value: $value};
                }
            }
            eval(fs.readFileSync('./assets/packages/expressions/em_javascript.js', {encoding: 'utf8'}));
            LEMradix = ',';
            LEMmode = 'survey';
            LEMalias2varName = {
                '$this->alias': 'java$this->sgqa',
                '$this->sgqa': 'java$this->sgqa'
            };
            LEMvarNameAttr = {
                'java$this->sgqa': {
                    'jsName':'java$this->sgqa',
                    'jsName_on':'java$this->sgqa',
                    'sgqa':'$this->sgqa',
                    'qid': '$qid',
                    'type':'N',
                    'default':'',
                    'rowdivid':'',
                    'onlynum': $this->onlynum,
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
}
