<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

trait ConditionHandlerHelperTrait
{
    /**
     * A placeholder name that is unique for the whole request.
     *
     * Handlers must never derive placeholder names from the column key or from
     * a per-handler index: several filters are merged into one criteria with
     * CDbCriteria::mergeWith(), which array_merges the params, so two filters
     * producing the same name silently lose one of the bound values. Yii uses
     * this same prefix + counter for exactly that reason.
     *
     * @return string
     */
    public function nextParamName(): string
    {
        return \CDbCriteria::PARAM_PREFIX . \CDbCriteria::$paramCount++;
    }

    /**
     * Strip anything that can not appear in a response column name, then quote
     * what is left.
     *
     * '#' is allowed because dual-scale questions store one column per scale
     * and separate them with it: Q42_S101#0 and Q42_S101#1
     * (common_helper.php:1930). Dropping it produced Q42_S1010 — a column that
     * does not exist — so those questions could not be filtered at all, even
     * though the key had already been validated against the field map. The
     * character is harmless here: the name is quoted, and inside an identifier
     * quote '#' is literal rather than a comment marker.
     *
     * @param string $key
     * @return string
     */
    public function sanitizeKey(string $key): string
    {
        $sanitizedKey = preg_replace('/[^a-zA-Z0-9_#-]/', '', $key);
        return App()->db->quoteColumnName($sanitizedKey);
    }
}
