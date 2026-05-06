<?php

namespace ls\tests;

/**
 * Test expression evaluation in PHP vs JS.
 *
 * @see https://bugs.limesurvey.org/view.php?id=12613
 * @since 2017-06-16
 * @group em
 * @todo Implement tests using the expressions below once survey import is available in test setup.
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
        ['', '((563168X136X5376.NAOK != " "))'],
        ['3', '((563168X136X5376.NAOK != " "))'],
        ['3', '((563168X136X5376.NAOK != ""))'],
        ['', '((563168X136X5376.NAOK != ""))'],
        ['', '((563168X136X5376.NAOK < " "))'],
        ['3', '((563168X136X5376.NAOK < " "))'],
        ['3', '((563168X136X5376.NAOK < "A"))'],
        ['3', '((563168X136X5376.NAOK <= " "))'],
        ['3', '((563168X136X5376.NAOK <= ""))'],
        ['', '((563168X136X5376.NAOK <= " "))'],
        ['', '((563168X136X5376.NAOK > " "))'],
        ['3', '((563168X136X5376.NAOK > " "))'],
        [' ', '((563168X136X5376.NAOK > " "))'],
        ['', '((563168X136X5376.NAOK >= ""))'],
        ['', '((563168X136X5376.NAOK >= " "))'],
        ['3', '((563168X136X5376.NAOK >= " "))'],
    ];

    public function testPlaceholder(): void
    {
        $this->assertNotEmpty($this->expressions);
    }
}
