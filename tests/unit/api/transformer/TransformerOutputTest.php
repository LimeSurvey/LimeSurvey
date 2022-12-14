<?php

namespace ls\tests\unit\api;

use ls\tests\TestBaseClass;
use LimeSurvey\Api\Transformer\Output\TransformerOutput;


/**
 * @testdox API TransformerOutput
 */
class TransformerOutputTest extends TestBaseClass
{
    /**
     * @testdox transform() Return data only includes mapped fields.
     */
    public function testReturnDataIncludesOnlyMappedFields()
    {
        $transformer = new TransformerOutput;
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
     * @testdox transform() Return mapped fields to new name.
     */
    public function testReturnDataMappedFieldsToNewName()
    {
        $transformer = new TransformerOutput;
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
}
