<?php

/**
 * LimeSurvey (tm)
 * Copyright (C) 2011-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
class ValidateExpressionCommand extends CConsoleCommand
{
    /**
     * @param int $surveyId
     * @param string $lang
     * @param string $type 'invitation' 'reminder' 'registration' 'confirmation' 'admin_notification' 'admin_detailed_notification'
     */
    public function actionEmail($surveyId, $lang, $type)
    {
        $_GET['type'] = $type;

        Yii::import('application.controllers.admin.ExpressionValidate', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::import('application.helpers.replacements_helper', true);
        Yii::import('application.helpers.common_helper', true);

        $c = new ExpressionValidate();
        $_SESSION['LEMsid'] = $surveyId;
        $c->email($surveyId, $lang);
    }
}
