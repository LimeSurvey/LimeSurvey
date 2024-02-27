<?php

namespace ls\tests\unit\api;

use LimeSurvey\Api\Transformer\Filter\Filter;
use ls\tests\TestBaseClass;

/**
 * @testdox API Filter
 */
class FilterTest extends TestBaseClass
{
    /**
     * @testdox Filter without extra parameters
     */
    public function testFilter()
    {
        $config = 'trim';
        $value = '  test  ';
        $filter = new Filter();
        $this->assertEquals('test', $filter->filter($value, $config));
    }

    /**
     * @testdox filter with extra parameters
     */
    public function testFilterParams()
    {
        $config = ['trim' => [' t!']];
        $value = '  test!  ';
        $filter = new Filter();
        $this->assertEquals('es', $filter->filter($value, $config));
    }

    /**
     * @testdox filter with invalid config
     */
    public function testFilterInvalid()
    {
        $config = ['sdf' => [' t!']];
        $value = '  test!  ';
        $filter = new Filter();
        $this->assertEquals($value, $filter->filter($value, $config));
    }
}
