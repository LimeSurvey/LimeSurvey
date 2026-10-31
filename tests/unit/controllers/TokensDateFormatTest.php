<?php

namespace ls\tests\controllers;

use ls\tests\DummyController;
use ls\tests\TestBaseClass;

/**
 * The sent, reminder sent and completed dates of a survey participant must be stored as 'Y-m-d H:i',
 * whatever date format the administrator uses (#17556).
 */
class TokensDateFormatTest extends TestBaseClass
{
    /** @var \Tokens */
    private $controller;

    /** @var mixed Original date format of the session */
    private $originalDateFormat;

    /**
     * Creates the participant controller.
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Yii::import('application.controllers.admin.Tokens', true);
        $this->controller = new \Tokens(new DummyController('dummyid'));
        $this->originalDateFormat = \Yii::app()->session['dateformat'];
    }

    /**
     * Restores the date format of the session.
     * @return void
     */
    protected function tearDown(): void
    {
        \Yii::app()->session['dateformat'] = $this->originalDateFormat;
        parent::tearDown();
    }

    /**
     * Converts a posted value with the given administrator date format.
     * @param int $dateFormat Date format id, see getDateFormatData()
     * @param mixed $value Posted value
     * @return string
     */
    private function convert(int $dateFormat, $value): string
    {
        \Yii::app()->session['dateformat'] = $dateFormat;
        $method = new \ReflectionMethod(\Tokens::class, 'convertTokenDateToDbFormat');
        return $method->invoke($this->controller, $value);
    }

    /**
     * @return array Date format id, posted value, expected stored value
     */
    public static function postedValueProvider(): array
    {
        return [
            'dd.mm.yyyy' => [1, '31.10.2026 11:54', '2026-10-31 11:54'],
            'dd/mm/yyyy' => [5, '31/10/2026 11:54', '2026-10-31 11:54'],
            'mm/dd/yyyy' => [11, '10/31/2026 11:54', '2026-10-31 11:54'],
            'd.m.yyyy' => [4, '5.9.2026 08:15', '2026-09-05 08:15'],
            'yyyy-mm-dd' => [6, '2026-10-31 11:54', '2026-10-31 11:54'],
            'already stored format' => [1, '2026-10-01 09:30', '2026-10-01 09:30'],
            'surrounding spaces' => [1, ' 31.10.2026 11:54 ', '2026-10-31 11:54'],
            'not sent' => [1, 'N', 'N'],
            'empty' => [1, '', 'N'],
            'null' => [1, null, 'N'],
            'sent without date' => [1, 'Y', 'Y'],
            'not a date' => [1, 'foo', 'foo'],
        ];
    }

    /**
     * A posted sent, reminder sent or completed value is stored in the participant table format.
     * @dataProvider postedValueProvider
     * @param int $dateFormat
     * @param mixed $value
     * @param string $expected
     * @return void
     */
    public function testConvertTokenDateToDbFormat(int $dateFormat, $value, string $expected)
    {
        $this->assertSame($expected, $this->convert($dateFormat, $value));
    }
}
