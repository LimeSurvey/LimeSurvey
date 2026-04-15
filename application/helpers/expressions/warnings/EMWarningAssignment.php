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

class EMWarningAssignment extends EMWarningBase
{
    /**
     * @param array $token
     */
    public function __construct(array $token)
    {
        $this->token = $token;
        $this->msg = gT('Assigning a new value to a variable.', 'unescaped');
        $this->helpLink = 'https://www.limesurvey.org/manual/Expression_Manager#Using_Assignment_Operator';
    }
}
