<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Transformer;
use LimeSurvey\Api\Transformer\TransformerException;

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
     * @testdox transform() Transformed data excludes fields with config false.
     */
    public function testTransformedDataExcludesFieldsWithFalseConfig()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => false,
            'last_name' => true,
            'age' => false
        ]);
        $transformedData = $transformer->transform([
            'first_name' => 'Kevin',
            'last_name' => 'Foster',
            'age' => 40
        ]);

        $this->assertEquals(['last_name' => 'Foster'], $transformedData);
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


    /**
     * @testdox transform() Casts all elements of a collection.
     */
    public function testTransformsAllElementsOfACollection()
    {
        $transformerUser = new Transformer;
        $transformerUser->setDataMap([
            'first_name' => true,
            'age' => ['type' => 'int'],
        ]);

        $transformer = new Transformer;
        $transformer->setDataMap([
            'users' => [
                'collection' => true,
                'transformer' => $transformerUser
            ]
        ]);

        $transformedData = $transformer->transform([
            'users' => [
                [
                    'first_name' => 'Kevin',
                    'last_name' => 'Foster',
                    'age' => '40'
                ],
                [
                    'first_name' => 'Bill',
                    'last_name' => 'Smith',
                    'age' => '51'
                ]
            ]
        ]);

        $this->assertEquals([
            'users' => [
                [
                    'first_name' => 'Kevin',
                    'age' => 40
                ],
                [
                    'first_name' => 'Bill',
                    'age' => 51
                ]
            ]
        ], $transformedData);
    }

    /**
     * @testdox transform() throws TransformerException on missing required field
     */
    public function testThrowsTransformerExceptionOnMissingRequiredField()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => ['required' => true],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ]);
    }

    /**
     * @testdox transform() throws TransformerException on missing required by matching operation
     */
    public function testThrowsTransformerExceptionOnMissingRequiredFieldByMatchingOperation()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => ['required' => 'create'],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ], ['operation' => 'create']);
    }


    /**
     * @testdox transform() throws TransformerException on missing required by matching operation array
     */
    public function testThrowsTransformerExceptionOnMissingRequiredFieldByMatchingOperationArray()
    {
        $this->expectException(
            TransformerException::class
        );

        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => ['required' => ['create', 'update']],
            'age' => true
        ]);
        $transformer->transform([
            'age' => 40
        ], ['operation' => 'create']);
    }

    /**
     * @testdox validate() returns array of error messages on failure
     */
    public function testValidateReturnsArrayOfErrorMessagesOnFailure()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => ['required' => true],
            'age' => true
        ]);
        $errors = $transformer->validate([
            'age' => 40
        ]);
        $this->assertNotEmpty($errors);
    }

    /**
     * @testdox validateAll() returns array of error messages on failure
     */
    public function testValidateAllReturnsArrayOfErrorMessagesOnFailure()
    {
        $transformer = new Transformer;
        $transformer->setDataMap([
            'first_name' => ['required' => true],
            'age' => true
        ]);
        $errors = $transformer->validateAll([
            ['age' => 40],
            ['age' => 51]
        ]);
        $this->assertNotEmpty($errors);
    }
}
