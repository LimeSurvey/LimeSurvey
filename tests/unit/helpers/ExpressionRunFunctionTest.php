<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Test ExpressionManager::RDP_RunFunction
 *
 * @see https://bugs.limesurvey.org/view.php?id=16223
 * @group em
 */
class ExpressionRunFunctionTest extends TestBaseClass
{
    /**
     * Value returned by self::returnFixedValue
     * @var mixed
     */
    private static $fixedValue;

    /**
     * Callable registered as EM function: return self::$fixedValue whatever the params
     * @return mixed
     */
    public static function returnFixedValue()
    {
        return self::$fixedValue;
    }

    /**
     * Callable registered as EM function: always throw an exception
     * @throws \Exception
     * @return void
     */
    public static function throwTestException()
    {
        throw new \Exception('Test exception');
    }

    /**
     * Get an ExpressionManager with the test functions registered
     * @return \ExpressionManager
     */
    private function getExpressionManager()
    {
        $em = new \ExpressionManager();
        $em->RegisterFunctions(array(
            'fixedvalue' => array(self::class . '::returnFixedValue', 'fixedvalue', 'Test', 'mixed fixedvalue()', '', 0, 1, 2, 3, 4, 5, 6),
            'fixedvaluelist' => array(self::class . '::returnFixedValue', 'fixedvaluelist', 'Test', 'mixed fixedvaluelist(arg1, ...)', '', -1),
            'fixedvaluemin' => array(self::class . '::returnFixedValue', 'fixedvaluemin', 'Test', 'mixed fixedvaluemin(arg1, ...)', '', -2),
            'throwexception' => array(self::class . '::throwTestException', 'throwexception', 'Test', 'void throwexception()', '', 1, 2, 3),
        ));
        return $em;
    }

    /**
     * Call the private RDP_RunFunction
     * @param \ExpressionManager $em
     * @param string $name function name
     * @param array $params
     * @return boolean|null
     */
    private function runFunction(\ExpressionManager $em, $name, array $params)
    {
        $method = new \ReflectionMethod($em, 'RDP_RunFunction');
        $method->setAccessible(true);
        return $method->invoke($em, array($name, 0, 'WORD'), $params);
    }

    /**
     * Get the value on top of the stack of an ExpressionManager
     * @param \ExpressionManager $em
     * @return mixed
     */
    private function getStackTopValue(\ExpressionManager $em)
    {
        $property = new \ReflectionProperty($em, 'RDP_stack');
        $property->setAccessible(true);
        $stack = $property->getValue($em);
        $this->assertNotEmpty($stack, 'Nothing was pushed on the stack');
        $token = end($stack);
        return $token[0];
    }

    /**
     * Falsy values, each must be pushed unchanged on the stack
     * @return array
     */
    public function falsyValueProvider()
    {
        return array(
            'false' => array(false),
            'null' => array(null),
            'empty array' => array(array()),
            'integer 0' => array(0),
            'string 0' => array('0'),
            'empty string' => array(''),
        );
    }

    /**
     * The result of a function is pushed as is, whatever the number of arguments.
     * Previously a function with 2 arguments returned false for any falsy result.
     * @dataProvider falsyValueProvider
     * @param mixed $value
     * @return void
     */
    public function testFalsyResultIsReturnedUnchanged($value)
    {
        self::$fixedValue = $value;
        for ($argsCount = 0; $argsCount <= 6; $argsCount++) {
            $em = $this->getExpressionManager();
            $params = array_fill(0, $argsCount, 'a');
            $this->assertTrue($this->runFunction($em, 'fixedvalue', $params), "Function with {$argsCount} arguments failed");
            $this->assertSame($value, $this->getStackTopValue($em), "Function with {$argsCount} arguments altered the result");
        }
        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'fixedvaluelist', array('a', 'b', 'c')));
        $this->assertSame($value, $this->getStackTopValue($em), 'Function with unlimited arguments altered the result');
    }

    /**
     * A truthy result is pushed as is.
     * @return void
     */
    public function testResultIsReturned()
    {
        self::$fixedValue = 'result';
        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'fixedvalue', array('a', 'b')));
        $this->assertSame('result', $this->getStackTopValue($em));
    }

    /**
     * Unknown function return false.
     * @return void
     */
    public function testUnknownFunction()
    {
        $em = $this->getExpressionManager();
        $this->assertFalse($this->runFunction($em, 'unknownfunction', array()));
    }

    /**
     * Wrong number of arguments return false and add an error.
     * @return void
     */
    public function testWrongArgumentsCount()
    {
        $em = $this->getExpressionManager();
        $this->assertFalse($this->runFunction($em, 'fixedvalue', array_fill(0, 7, 'a')));
        $this->assertCount(1, $em->GetErrors());

        $em = $this->getExpressionManager();
        $this->assertFalse($this->runFunction($em, 'fixedvaluemin', array()));
        $this->assertCount(1, $em->GetErrors());

        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'fixedvaluemin', array('a')));
        $this->assertEmpty($em->GetErrors());
    }

    /**
     * Exception in function return false and add an error, whatever the number of arguments.
     * @return void
     */
    public function testException()
    {
        foreach (array(1, 2, 3) as $argsCount) {
            $em = $this->getExpressionManager();
            $this->assertFalse($this->runFunction($em, 'throwexception', array_fill(0, $argsCount, 'a')), "Function with {$argsCount} arguments did not fail");
            $this->assertCount(1, $em->GetErrors(), "Function with {$argsCount} arguments did not add an error");
        }
    }

    /**
     * When only parsing, the function is not called and 1 is pushed.
     * @return void
     */
    public function testOnlyParse()
    {
        self::$fixedValue = 'result';
        $em = $this->getExpressionManager();
        $property = new \ReflectionProperty($em, 'RDP_onlyparse');
        $property->setAccessible(true);
        $property->setValue($em, true);
        $this->assertTrue($this->runFunction($em, 'fixedvalue', array('a')));
        $this->assertSame(1, $this->getStackTopValue($em));
        $this->assertTrue($this->runFunction($em, 'throwexception', array('a')));
        $this->assertEmpty($em->GetErrors());
    }

    /**
     * Math functions with non numeric parameter.
     * @return void
     */
    public function testMathFunctionsWithNonNumeric()
    {
        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'sqrt', array('a')));
        $this->assertNan($this->getStackTopValue($em));

        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'sqrt', array('4')));
        $this->assertSame(2.0, $this->getStackTopValue($em));

        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'pow', array('a', 2)));
        $this->assertFalse($this->getStackTopValue($em));

        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'pow', array('2', '3')));
        $this->assertEquals(8, $this->getStackTopValue($em));
    }

    /**
     * substr parameters and expected result
     * @return array
     */
    public function substrProvider()
    {
        return array(
            'start 0 with length' => array(array('abcdef', 0, 2), 'ab'),
            'start as string with length' => array(array('abcdef', '1', '2'), 'bc'),
            'negative start' => array(array('abcdef', -2), 'ef'),
            'no length' => array(array('abcdef', 2), 'cdef'),
            'length 0' => array(array('abcdef', 2, 0), ''),
            'multibyte' => array(array('äöüß', 1, 2), 'öü'),
            'start at end of string' => array(array('abcdef', 6), ''),
            'start after end of string' => array(array('abcdef', 10, 2), ''),
            'negative start before beginning of string' => array(array('abcdef', -10, 2), 'ab'),
            'negative length before start' => array(array('abcdef', 2, -10), ''),
            'length after end of string' => array(array('abcdef', 2, 100), 'cdef'),
            'numeric question value as start' => array(array('abcdef', '2.0000000000'), 'cdef'),
            'leading zero start' => array(array('abcdef', '01', '02'), 'bc'),
            'float start is truncated' => array(array('abcdef', '1.5', 2.9), 'bc'),
            'non numeric start' => array(array('abcdef', 'a'), false),
            'non numeric start with length' => array(array('abcdef', 'a', 2), false),
            'non numeric length' => array(array('abcdef', 1, 'a'), false),
            'empty start' => array(array('abcdef', ''), false),
            'empty length' => array(array('abcdef', 1, ''), false),
        );
    }

    /**
     * substr must accept numeric start and length (truncated to integer) and return false for non numeric ones.
     * @dataProvider substrProvider
     * @param array $params
     * @param string|false $expected
     * @return void
     */
    public function testSubstr(array $params, $expected)
    {
        $em = $this->getExpressionManager();
        $this->assertTrue($this->runFunction($em, 'substr', $params), 'substr failed: ' . print_r($em->GetErrors(), true));
        $this->assertSame($expected, $this->getStackTopValue($em));
    }
}
