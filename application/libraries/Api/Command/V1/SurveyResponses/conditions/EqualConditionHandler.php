<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class EqualConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'equal') {
            return true;
        }
        return false;
    }

    public function execute($key, $value): object
    {
        $criteria = new \CDbCriteria();

        if (!is_array($key)) {
            $key = [$key];
        }

        $conditions = [];
        $params = [];

        foreach ($key as $rawKey) {
            $quotedKey = $this->sanitizeKey($rawKey);
            $strippedKey = $this->stripKey($quotedKey);

            $conditions[] = "$quotedKey = :{$strippedKey}Value";
            $params[":{$strippedKey}Value"] = $value;
        }

        $criteria->condition = implode(' OR ', $conditions);
        $criteria->params = $params;

        return $criteria;
    }
}
