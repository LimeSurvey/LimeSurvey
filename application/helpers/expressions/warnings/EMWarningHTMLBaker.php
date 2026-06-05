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

/**
 * Small helper class to compose HTML from $warnings.
 */
class EMWarningHTMLBaker
{
    /**
     * Bake HTML out of $warnings.
     *
     * @param EMWarningInterface[] Array of EM warnings
     * @return string
     */
    public function getWarningHTML(array $warnings)
    {
        $message = "<strong>"
            . ngT(
                "This question has at least {n} warning.|This question has at least {n} warnings.",
                count($warnings),
                'html'
            )
            . "</strong>";
        $message .= "<ul class='list-unstyled small'>";
        $warningsDone = [];
        foreach ($warnings as $aWarning) {
            if (!in_array($aWarning, $warningsDone)) {
                $message .= "<li>";
                if ($aWarning->hasHelpLink()) {
                    $message .= $aWarning->bakeHelpLink();
                } else {
                    $message .= $aWarning->getMessage();
                }
                $message .= "</li>";
            }
            $warningsDone[] = $aWarning->getMessage();
        }
        $message .= "</ul>";

        return App()->getController()->widget('ext.AlertWidget.AlertWidget', [
            'text' => $message,
            'type' => 'warning',
        ], true);
        // TODO: Factor out in warning classes OOP
    }
}
