<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

trait ConditionHandlerHelperTrait
{
    /**
     * @param string $key
     * @return string
     */
    public function sanitizeKey(string $key): string
    {
        $sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        return App()->db->quoteColumnName($sanitizedKey);
    }

    /**
     * More strict strip of a key without quotation
     * @param string $key
     * @return string
     */
    public function stripKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
    }
}
