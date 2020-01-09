<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2020 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class LSYii_XssValidator extends CValidator
{
    
    /**
     * Trim input
     * @var boolean
     */
    public $trim = true;
    
    /**
     * Remove newlines
     * @var boolean
     */
    public $rmnewlines = true;

    /**
     * allow HTML
     * @var boolean
     */
    public $allowHTML = true;

    /**
     * Allow all css (unused if no HTML allowed)
     * @var boolean
     */
    public $allowCSS = true;


    /**
     * Remove any script or dangerous HTML
     *
     * @param string $value
     * @return string
     */
    public function filterHTML($value)
    {
        $filter = new CHtmlPurifier();
        $filter->options = array(
            'AutoFormat.RemoveEmpty'=>false,
            'Core.NormalizeNewlines'=>false,
            'CSS.AllowTricky'=>$this->allowCSS, // Allow display:none; (and other)
            'HTML.SafeObject'=>true, // To allow including youtube
            'Output.FlashCompat'=>true,
            'Attr.EnableID'=>true, // Allow to set id
            'Attr.AllowedFrameTargets'=>array('_blank', '_self'),
            'URI.AllowedSchemes'=>array(
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                )
        );
        // To allow script BUT purify : HTML.Trusted=true (plugin idea for admin or without XSS filtering ?)

        /** Start to get complete filtered value with  url decode {QCODE} (bug #09300). This allow only question number in url, seems OK with XSS protection **/
        $sFiltered = preg_replace('#%7B([a-zA-Z0-9\.]*)%7D#', '{$1}', $filter->purify($value));
        Yii::import('application.helpers.expressions.em_core_helper', true); // Already imported in em_manager_helper.php ?
        $oExpressionManager = new ExpressionManager;
        /**  We get 2 array : one filtered, other unfiltered **/
        $aValues = $oExpressionManager->asSplitStringOnExpressions($value); // Return array of array : 0=>the string,1=>string length,2=>string type (STRING or EXPRESSION)
        $aFilteredValues = $oExpressionManager->asSplitStringOnExpressions($sFiltered); // Same but for the filtered string
        $bCountIsOk = count($aValues) == count($aFilteredValues);
        /** Construction of new string with unfiltered EM and filtered HTML **/
        $sNewValue = "";
        foreach ($aValues as $key=>$aValue) {
            if ($aValue[2] == "STRING") {
                $sNewValue .= $bCountIsOk ? $aFilteredValues[$key][0] : $filter->purify($aValue[0]); // If EM is broken : can throw invalid $key
            } else {
                $sExpression = trim($aValue[0], '{}');
                $sNewValue .= "{";
                $aParsedExpressions = $oExpressionManager->Tokenize($sExpression, true);
                foreach ($aParsedExpressions as $aParsedExpression) {
                    if ($aParsedExpression[2] == 'DQ_STRING') {
                        $sNewValue .= "\"".(string) $filter->purify($aParsedExpression[0])."\""; // This disallow complex HTML construction with XSS
                    } elseif ($aParsedExpression[2] == 'SQ_STRING') {
                        $sNewValue .= "'".(string) $filter->purify($aParsedExpression[0])."'";
                    } else {
                        $sNewValue .= $aParsedExpression[0];
                    }
                }
                $sNewValue .= "}";
            }
        }
        gc_collect_cycles(); // To counter a high memory usage of HTML-Purifier
        return $sNewValue;
    }

    protected function validateAttribute($object, $attribute)
    {
        if($this->rmnewlines) {
            $object->$attribute = preg_replace("/(\r?\n\r?)/","", $object->$attribute);
        }
        
        if ($this->allowHTML) {
            $object->$attribute = $this->filterHTML($object->$attribute);
        } else {
            $object->$attribute = htmlentities(strip_tags($object->$attribute));
        }

        if($this->trim) {
            $object->$attribute = trim($object->$attribute);
        }
    }
}
