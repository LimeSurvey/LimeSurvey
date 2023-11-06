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
        // TODO: Factor out in warning classes OOP
        $html = "<div class='alert alert-warning'>";
        $html .= "<strong class=''>"
            . ngT(
                "This question has at least {n} warning.|This question has at least {n} warnings.",
                count($warnings),
                'html'
            )
            . "</strong>";
        $html .= "<ul class='list-unstyled small text-warning'>";
        $warningsDone = array();
        foreach ($warnings as $aWarning) {
            if (!in_array($aWarning->getMessage(), $warningsDone)) {
                $html .= "<li>";
                if ($aWarning->hasHelpLink()) {
                    $html .= $aWarning->bakeHelpLink();
                } else {
                    $html .= $aWarning->getMessage();
                }
                $html .= "</li>";
            }
            $warningsDone[] = $aWarning->getMessage();
        }
        $html .= "</ul>";
        $html .= "</div>";
        return $html;
    }
}
