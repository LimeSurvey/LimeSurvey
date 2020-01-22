<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
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
    /**
     *
     */
    public function __construct(array $token)
    {
        $this->token = $token;
        $this->msg = gT("Usage of + with numeric value, see manual about usage of sum.", 'unescaped');
        $this->helpLink = "https://manual.limesurvey.org/Expression_Manager#Warning_with_plus_operator_.28.2B.29";
    }
}
