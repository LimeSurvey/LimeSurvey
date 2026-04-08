<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\ApiConfig;

/**
 * @testdox API Config
 */
class ApiConfigTest extends TestBaseClass
{
    /**
     * @testdox getPath() returns value at path
     */
    public function testGetPathReturnsValueAtPath()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => 123
                ]
            ]
        ];
        $config = new ApiConfig($data);
        $this->assertEquals(123, $config->getPath('a.b.c'));
    }

    /**
     * @testdox setPath() sets value at path
     */
    public function testSetPathSetsValueAtPath()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => 123
                ]
            ]
        ];
        $config = new ApiConfig($data);
        $config->setPath('a.b.c', 456);
        $this->assertEquals(456, $data['a']['b']['c']);
    }

    /**
     * @testdox setPath() initializes parents
     */
    public function testSetPathInitializesParents()
    {
        $data = [
            'a' => []
        ];
        $config = new ApiConfig($data);
        $config->setPath('a.b.c', 789);
        $this->assertEquals(789, $data['a']['b']['c']);
    }

     /**
     * @testdox getPath() does not initialize parents
     */
    public function testGetPathDoesnotInitializeParents()
    {
        $data = [
            'a' => []
        ];
        $config = new ApiConfig($data);
        $this->assertEquals(null, $config->getPath('a.b.c'));
    }
}
