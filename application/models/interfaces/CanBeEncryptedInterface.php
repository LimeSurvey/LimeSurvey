<?php

/**
 * All models that can have encrypted values implements this interface.
 */
interface CanBeEncryptedInterface
{
    /**
     * @param int $iSurveyId
     * @return Attribute[]
     */
    public function getAllEncryptedAttributes($iSurveyId = 0);
}
