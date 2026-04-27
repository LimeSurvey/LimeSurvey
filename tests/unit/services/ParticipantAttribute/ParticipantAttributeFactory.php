<?php

namespace ls\tests\unit\services\ParticipantAttribute;

use LimeSurvey\Models\Services\ParticipantAttributeService;

class ParticipantAttributeFactory
{
    /**
     * @param ParticipantAttributeMockSet|null $mockSet
     * @return ParticipantAttributeService
     */
    public function make(ParticipantAttributeMockSet $mockSet = null): ParticipantAttributeService
    {
        $mockSet = (new ParticipantAttributeMockSetFactory())->make($mockSet);

        return new ParticipantAttributeService(
            $mockSet->modelParticipantAttributeName,
            $mockSet->modelParticipantAttributeNameLang,
            $mockSet->modelParticipantAttributeValue
        );
    }
}