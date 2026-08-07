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

class EMWarningInvalidComparison extends EMWarningBase
{
    /**
     * @param array $token
     */
    public function __construct(array $token)
    {
        $this->token = $token;
        $this->msg = gT("This expression uses a possibly invalid comparison. Are you sure you didn't mean to do a numerical comparison? See manual for more information.", 'unescaped');
        $this->helpLink = "https://www.limesurvey.org/manual/Expression_Manager#Warning_with_mismatch_between_number_and_string_and_alphabetic_comparison";
    }
}
