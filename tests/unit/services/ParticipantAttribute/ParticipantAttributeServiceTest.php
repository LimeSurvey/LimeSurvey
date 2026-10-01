<?php

namespace ls\tests\unit\services\ParticipantAttribute;

use ls\tests\TestBaseClass;

class ParticipantAttributeServiceTest extends TestBaseClass
{


    /**
     * @testdox saveParticipantAttribute() saves attribute name and localization, returns ID
     */
    public function testSaveParticipantAttribute()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $attributeData = [
            'type' => 'TB',
            'encrypted' => 'N'
        ];
        $attributeName = 'test_attribute';

        // Mock the base attribute save
        $mockSet->modelParticipantAttributeName->shouldReceive('save')
            ->once()
            ->andReturn(true);
        $mockSet->modelParticipantAttributeName->shouldReceive('getPrimaryKey')
            ->once()
            ->andReturn(1);

        // Mock the localization save
        $mockSet->modelParticipantAttributeNameLang->shouldReceive('save')
            ->once()
            ->andReturn(true);

        // Since type is 'TB' (not 'DD'), no attribute values should be saved
        $mockSet->modelParticipantAttributeValue->shouldReceive('save')
            ->never();

        $attributeId = $participantAttributeService->saveParticipantAttribute($attributeData, $attributeName);

        $this->assertIsInt($attributeId);
        $this->assertEquals(1, $attributeId);
    }


    /**
     * @testdox saveParticipantAttributeName() saves base attribute with correct data and returns ID
     */
    public function testSaveParticipantAttributeName()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $attributeData = [
            'type' => 'DD',
            'encrypted' => 'Y'
        ];
        $attributeName = 'dropdown_attribute';

        $mockSet->modelParticipantAttributeName->shouldReceive('setAttributes')
            ->once()
            ->with(\Mockery::on(function ($data) use ($attributeData, $attributeName) {
                return $data['attribute_type'] === $attributeData['type']
                    && $data['encrypted'] === $attributeData['encrypted']
                    && $data['defaultname'] === $attributeName
                    && $data['visible'] === 'Y';
            }), false);

        $mockSet->modelParticipantAttributeName->shouldReceive('save')
            ->once()
            ->andReturn(true);
        $mockSet->modelParticipantAttributeName->shouldReceive('getPrimaryKey')
            ->once()
            ->andReturn(5);

        $attributeId = $participantAttributeService->saveParticipantAttributeName($attributeData, $attributeName);

        $this->assertIsInt($attributeId);
        $this->assertEquals(5, $attributeId);
    }



    /**
     * @testdox saveParticipantAttributeL10n() saves localized attribute name with correct data
     */
    public function testSaveParticipantAttributeL10n()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $attributeName = 'Test Attribute';
        $attributeId = 1;

        // Verify the correct attributes are set
        $mockSet->modelParticipantAttributeNameLang->shouldReceive('setAttributes')
            ->once()
            ->with(\Mockery::on(function ($data) use ($attributeName, $attributeId) {
                return $data['attribute_id'] === $attributeId
                    && $data['attribute_name'] === $attributeName
                    && $data['lang'] === 'en'; // or whatever the default language is
            }), false);

        $mockSet->modelParticipantAttributeNameLang->shouldReceive('save')
            ->once()
            ->andReturn(true);

        // Call the void method - test passes if no exception is thrown
        $participantAttributeService->saveParticipantAttributeL10n($attributeName, $attributeId);

        // Mockery will verify the expectations were met
        $this->expectNotToPerformAssertions();
    }


    /**
     * @testdox saveDropdownAttributeOptions() saves options for dropdown type with correct data
     */
    public function testSaveDropdownAttributeOptions()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $attributeId = 1;
        $attributeData = [
            'type' => 'DD',
            'type_options' => '["Option 1","Option 2","Option 3"]'
        ];

        // Verify each option is saved with correct data
        $mockSet->modelParticipantAttributeValue->shouldReceive('setAttributes')
            ->times(3)
            ->with(\Mockery::on(function ($data) use ($attributeId) {
                return $data['attribute_id'] === $attributeId
                    && isset($data['value'])
                    && in_array($data['value'], ['Option 1', 'Option 2', 'Option 3']);
            }), false);

        $mockSet->modelParticipantAttributeValue->shouldReceive('save')
            ->times(3)
            ->andReturn(true);

        $participantAttributeService->saveDropdownAttributeOptions($attributeId, $attributeData);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @testdox saveDropdownAttributeOptions() does nothing for non-dropdown types
     */
    public function testSaveDropdownAttributeOptionsSkipsNonDropdown()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $attributeData = [
            'type' => 'TB', // Text box, not dropdown
            'type_options' => '["Option 1","Option 2"]'
        ];

        $mockSet->modelParticipantAttributeValue->shouldReceive('save')
            ->never();

        $participantAttributeService->saveDropdownAttributeOptions(1, $attributeData);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @testdox decodeJsonEncodedTypeOptions() correctly decodes JSON options
     */
    public function testDecodeJsonEncodedTypeOptions()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $jsonOptions = '["Option A","Option B","Option C"]';
        $decoded = $participantAttributeService->decodeJsonEncodedTypeOptions($jsonOptions);

        $this->assertIsArray($decoded);
        $this->assertCount(3, $decoded);
        $this->assertEquals('Option A', $decoded['Option A']);
        $this->assertEquals('Option B', $decoded['Option B']);
        $this->assertEquals('Option C', $decoded['Option C']);
    }

    /**
     * @testdox decodeJsonEncodedTypeOptions() returns empty array for invalid JSON
     */
    public function testDecodeJsonEncodedTypeOptionsInvalidJson()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $invalidJson = 'not valid json';
        $decoded = $participantAttributeService->decodeJsonEncodedTypeOptions($invalidJson);

        $this->assertIsArray($decoded);
        $this->assertEmpty($decoded);
    }

    /**
     * @testdox decodeJsonEncodedTypeOptions() returns empty array for empty string
     */
    public function testDecodeJsonEncodedTypeOptionsEmptyString()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $decoded = $participantAttributeService->decodeJsonEncodedTypeOptions('');

        $this->assertIsArray($decoded);
        $this->assertEmpty($decoded);
    }


    /**
     * @testdox normalizeAttributeDataset() fills in missing keys with default values
     */
    public function testNormalizeAttributeDatasetWithMissingKeys()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $partialData = [
            'type' => 'DD',
            'encrypted' => 'Y'
        ];

        $normalized = $participantAttributeService->normalizeAttributeDataset($partialData);

        $this->assertEquals('DD', $normalized['type']);
        $this->assertEquals('Y', $normalized['encrypted']);
        $this->assertEquals('', $normalized['description']);
        $this->assertEquals('N', $normalized['mandatory']);
        $this->assertEquals('Y', $normalized['show_register']);
        $this->assertEquals('[]', $normalized['type_options']);
        $this->assertEquals('', $normalized['cpdbmap']);
    }

    /**
     * @testdox normalizeAttributeDataset() preserves existing values
     */
    public function testNormalizeAttributeDatasetPreservesExistingValues()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $completeData = [
            'description' => 'Custom description',
            'mandatory' => 'Y',
            'encrypted' => 'Y',
            'show_register' => 'N',
            'type' => 'DD',
            'type_options' => '["Option 1","Option 2"]',
            'cpdbmap' => 'custom_map'
        ];

        $normalized = $participantAttributeService->normalizeAttributeDataset($completeData);

        $this->assertEquals($completeData, $normalized);
    }

    /**
     * @testdox normalizeAttributeDataset() normalizes boolean-like values to Y/N
     */
    public function testNormalizeAttributeDatasetNormalizesBooleanValues()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $dataWithVariousBooleans = [
            'mandatory' => true,
            'encrypted' => 1,
            'show_register' => 'y'
        ];

        $normalized = $participantAttributeService->normalizeAttributeDataset($dataWithVariousBooleans);

        $this->assertEquals('Y', $normalized['mandatory']);
        $this->assertEquals('Y', $normalized['encrypted']);
        $this->assertEquals('Y', $normalized['show_register']);

        $dataWithFalsyValues = [
            'mandatory' => false,
            'encrypted' => 0,
            'show_register' => 'N'
        ];

        $normalized = $participantAttributeService->normalizeAttributeDataset($dataWithFalsyValues);

        $this->assertEquals('N', $normalized['mandatory']);
        $this->assertEquals('N', $normalized['encrypted']);
        $this->assertEquals('N', $normalized['show_register']);
    }

    /**
     * @testdox normalizeAttributeDataset() converts array type_options to JSON string
     */
    public function testNormalizeAttributeDatasetConvertsArrayToJson()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $dataWithArrayOptions = [
            'type_options' => ['Option 1', 'Option 2', 'Option 3']
        ];

        $normalized = $participantAttributeService->normalizeAttributeDataset($dataWithArrayOptions);

        $this->assertIsString($normalized['type_options']);
        $decoded = json_decode($normalized['type_options'], true);
        $this->assertEquals(['Option 1', 'Option 2', 'Option 3'], $decoded);
    }

    /**
     * @testdox normalizeAttributeDataset() replaces invalid JSON with empty array
     */
    public function testNormalizeAttributeDatasetReplacesInvalidJson()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $dataWithInvalidJson = [
            'type_options' => 'not valid json'
        ];

        $normalized = $participantAttributeService->normalizeAttributeDataset($dataWithInvalidJson);

        $this->assertEquals('[]', $normalized['type_options']);
    }

    /**
     * @testdox normalizeAttributeDataset() handles empty array input
     */
    public function testNormalizeAttributeDatasetHandlesEmptyArray()
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make();
        $participantAttributeService = (new ParticipantAttributeFactory())->make($mockSet);

        $normalized = $participantAttributeService->normalizeAttributeDataset([]);

        $this->assertEquals('', $normalized['description']);
        $this->assertEquals('N', $normalized['mandatory']);
        $this->assertEquals('N', $normalized['encrypted']);
        $this->assertEquals('Y', $normalized['show_register']);
        $this->assertEquals('TB', $normalized['type']);
        $this->assertEquals('[]', $normalized['type_options']);
        $this->assertEquals('', $normalized['cpdbmap']);
    }
}