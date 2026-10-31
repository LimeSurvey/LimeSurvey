<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class EMWarningPlusOperator extends EMWarningBase
{
    /** Both operands are numeric: + is an addition */
    const NUMERIC = 'numeric';

    /** One operand is a string literal or a string function result: + is a concatenation */
    const STRING = 'string';

    /** Operands with a value not known yet (e.g. an unanswered question in the logic file) */
    const UNKNOWN = 'unknown';

    /**
     * @param array $token
     * @param string $operandType One of self::NUMERIC, self::STRING or self::UNKNOWN
     */
    public function __construct(array $token, $operandType = self::NUMERIC)
    {
        $this->token = $token;
        switch ($operandType) {
            case self::STRING:
                $this->msg = gT("Usage of + with string value, see manual about usage of join.", 'unescaped');
                break;
            case self::UNKNOWN:
                $this->msg = gT("Usage of + with string or numeric value, see manual about usage of sum and join.", 'unescaped');
                break;
            default:
                $this->msg = gT("Usage of + with numeric value, see manual about usage of sum.", 'unescaped');
        }
        $this->helpLink = "https://www.limesurvey.org/manual/Expression_Manager#Warning_with_plus_operator_.28.2B.29";
    }
}
