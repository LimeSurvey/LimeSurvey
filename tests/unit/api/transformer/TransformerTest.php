<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;

/**
 * @testdox API Transformer
 */
class TransformerOutputTest extends TestBaseClass
{
    /**
     * @testdox transform() Transformed data only includes fields specified in map.
     */
    public function testTransformedDataIncludesOnlyFieldsSpecifiedInMap()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => true,
            'age' => true
        ]);
        $transformedData = $transformer->transform([
            'first_name' => 'Kevin',
            'last_name' => 'Foster',
            'age' => 40
        ]);

        $this->assertEquals([
            'first_name' => 'Kevin',
            'age' => 40
        ], $transformedData);
    }

    /**
     * @testdox transform() Maps to specified output fields.
     */
    public function testMapsToSpecifiedOutputFields()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => 'given_name',
            'age' => 'years_of_existence'
        ]);
        $transformedData = $transformer->transform([
            'first_name' => 'Kevin',
            'last_name' => 'Foster',
            'age' => 40
        ]);

        $this->assertEquals([
            'given_name' => 'Kevin',
            'years_of_existence' => 40
        ], $transformedData);
    }

    /**
     * @testdox transform() Maps to specified output fields via key config.
     */
    public function testMapsToSpecifiedOutputFieldsViaKeyConfig()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => ['key' => 'given_name'],
            'age' => ['key' => 'years_of_existence']
        ]);
        $transformedData = $transformer->transform([
            'first_name' => 'Kevin',
            'last_name' => 'Foster',
            'age' => 40
        ]);

        $this->assertEquals([
            'given_name' => 'Kevin',
            'years_of_existence' => 40
        ], $transformedData);
    }

    /**
     * @testdox transform() Casts to primitive types.
     */
    public function testCastsToPrimitiveType()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'enable' => ['type' => 'boolean'],
            'fraction' => ['type' => 'float'],
            'age' => ['type' => 'int'],
            'name' => ['type' => 'string'],
        ]);

        $transformedDataA = $transformer->transform([
            'enable' => '1',
            'fraction' => '1.33',
            'age' => '40',
            'name' => 123
        ]);
        $this->assertEquals([
            'enable' => true,
            'fraction' => 1.33,
            'age' => 40,
            'name' => '123'
        ], $transformedDataA);

        $transformedDataA = $transformer->transform([
            'enable' => '0',
            'fraction' => '1.33',
            'age' => '40',
            'name' => false
        ]);
        $this->assertEquals([
            'enable' => false,
            'fraction' => 1.33,
            'age' => 40,
            'name' => ''
        ], $transformedDataA);
    }

    /**
     * @testdox transform() Casts to via callable.
     */
    public function testCastsViaCallable()
    {
        $castBoolean = function($value) {
            return (boolean) $value;
        };
        $castFloat = function ($value) {
            return (float) $value;
        };
        $castInt = function ($value) {
            return (int) $value;
        };
        $castString = function ($value) {
            return (string) $value;
        };


        $transformer = new Transformer;
        $transformer->setDataMap([
            'enable' => ['type' => $castBoolean],
            'fraction' => ['type' => $castFloat],
            'age' => ['type' => $castInt],
            'name' => ['type' => $castString],
        ]);

        $transformedDataA = $transformer->transform([
            'enable' => '1',
            'fraction' => '1.33',
            'age' => '40',
            'name' => 123
        ]);
        $this->assertEquals([
            'enable' => true,
            'fraction' => 1.33,
            'age' => 40,
            'name' => '123'
        ], $transformedDataA);

        $transformedDataA = $transformer->transform([
            'enable' => '0',
            'fraction' => '1.33',
            'age' => '40',
            'name' => false
        ]);
        $this->assertEquals([
            'enable' => false,
            'fraction' => 1.33,
            'age' => 40,
            'name' => ''
        ], $transformedDataA);
    }
}
