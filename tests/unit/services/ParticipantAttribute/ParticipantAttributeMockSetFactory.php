<?php

namespace ls\tests\unit\services\ParticipantAttribute;

use Mockery;
use ParticipantAttributeName;
use ParticipantAttributeNameLang;
use ParticipantAttributeValue;

class ParticipantAttributeMockSetFactory
{
    /**
     * @param ParticipantAttributeMockSet|null $init
     * @return ParticipantAttributeMockSet
     */
    public function make(ParticipantAttributeMockSet $init = null)
    {
        $mockSet = new ParticipantAttributeMockSet();

        $mockSet->modelParticipantAttributeName = ($init && isset($init->modelParticipantAttributeName))
            ? $init->modelParticipantAttributeName
            : $this->getMockParticipantAttributeName();

        $mockSet->modelParticipantAttributeName = ($init && isset($init->modelParticipantAttributeName))
            ? $init->modelParticipantAttributeName
            : $this->getMockModelParticipantAttributeName($mockSet->modelParticipantAttributeName);

        $mockSet->modelParticipantAttributeNameLang = ($init && isset($init->modelParticipantAttributeNameLang))
            ? $init->modelParticipantAttributeNameLang
            : $this->getMockParticipantAttributeNameLang();

        $mockSet->modelParticipantAttributeNameLang = ($init && isset($init->modelParticipantAttributeNameLang))
            ? $init->modelParticipantAttributeNameLang
            : $this->getMockModelParticipantAttributeNameLang($mockSet->modelParticipantAttributeNameLang);

        $mockSet->modelParticipantAttributeValue = ($init && isset($init->modelParticipantAttributeValue))
            ? $init->modelParticipantAttributeValue
            : $this->getMockParticipantAttributeValue();

        $mockSet->modelParticipantAttributeValue = ($init && isset($init->modelParticipantAttributeValue))
            ? $init->modelParticipantAttributeValue
            : $this->getMockModelParticipantAttributeValue($mockSet->modelParticipantAttributeValue);

        return $mockSet;
    }

    private function getMockParticipantAttributeName(): ParticipantAttributeName
    {
        $participantAttributeName = Mockery::mock(ParticipantAttributeName::class)
            ->makePartial();
        $participantAttributeName->shouldReceive('save')
            ->andReturn(true);
        $participantAttributeName->shouldReceive('validate')
            ->andReturn(true);
        $participantAttributeName->shouldReceive('getErrors')
            ->andReturn([]);
        $participantAttributeName->shouldReceive('setAttributes')
            ->passthru();
        $participantAttributeName->setAttributes([]);
        $participantAttributeName->shouldReceive('getAttributes')
            ->passthru();
        $participantAttributeName->getAttributes([]);

        return $participantAttributeName;
    }

    private function getMockModelParticipantAttributeName($participantAttributeName): ParticipantAttributeName
    {
        $modelParticipantAttributeName = Mockery::mock(ParticipantAttributeName::class)
            ->makePartial();
        $modelParticipantAttributeName->shouldReceive('findByPk')
            ->andReturn($participantAttributeName);
        $modelParticipantAttributeName->shouldReceive('findAll')
            ->andReturn([$participantAttributeName]);
        $modelParticipantAttributeName->shouldReceive('findAllByAttributes')
            ->andReturn([$participantAttributeName]);
        $modelParticipantAttributeName->shouldReceive('model')
            ->andReturn($modelParticipantAttributeName);

        return $modelParticipantAttributeName;
    }

    private function getMockParticipantAttributeNameLang(): ParticipantAttributeNameLang
    {
        $participantAttributeNameLang = Mockery::mock(ParticipantAttributeNameLang::class)
            ->makePartial();
        $participantAttributeNameLang->shouldReceive('save')
            ->andReturn(true);
        $participantAttributeNameLang->shouldReceive('validate')
            ->andReturn(true);
        $participantAttributeNameLang->shouldReceive('getErrors')
            ->andReturn([]);
        $participantAttributeNameLang->shouldReceive('setAttributes')
            ->passthru();
        $participantAttributeNameLang->setAttributes([]);
        $participantAttributeNameLang->shouldReceive('getAttributes')
            ->passthru();
        $participantAttributeNameLang->getAttributes([]);

        return $participantAttributeNameLang;
    }

    private function getMockModelParticipantAttributeNameLang($participantAttributeNameLang): ParticipantAttributeNameLang
    {
        $modelParticipantAttributeNameLang = Mockery::mock(ParticipantAttributeNameLang::class)
            ->makePartial();
        $modelParticipantAttributeNameLang->shouldReceive('findByAttributes')
            ->andReturn($participantAttributeNameLang);
        $modelParticipantAttributeNameLang->shouldReceive('findAll')
            ->andReturn([$participantAttributeNameLang]);
        $modelParticipantAttributeNameLang->shouldReceive('model')
            ->andReturn($modelParticipantAttributeNameLang);

        return $modelParticipantAttributeNameLang;
    }

    private function getMockParticipantAttributeValue(): ParticipantAttributeValue
    {
        $participantAttributeValue = Mockery::mock(ParticipantAttributeValue::class)
            ->makePartial();
        $participantAttributeValue->shouldReceive('save')
            ->andReturn(true);
        $participantAttributeValue->shouldReceive('validate')
            ->andReturn(true);
        $participantAttributeValue->shouldReceive('getErrors')
            ->andReturn([]);
        $participantAttributeValue->shouldReceive('setAttributes')
            ->passthru();
        $participantAttributeValue->setAttributes([]);
        $participantAttributeValue->shouldReceive('getAttributes')
            ->passthru();
        $participantAttributeValue->getAttributes([]);

        return $participantAttributeValue;
    }

    private function getMockModelParticipantAttributeValue($participantAttributeValue): ParticipantAttributeValue
    {
        $modelParticipantAttributeValue = Mockery::mock(ParticipantAttributeValue::class)
            ->makePartial();
        $modelParticipantAttributeValue->shouldReceive('findByPk')
            ->andReturn($participantAttributeValue);
        $modelParticipantAttributeValue->shouldReceive('findAll')
            ->andReturn([$participantAttributeValue]);
        $modelParticipantAttributeValue->shouldReceive('model')
            ->andReturn($modelParticipantAttributeValue);

        return $modelParticipantAttributeValue;
    }
}