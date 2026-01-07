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
}