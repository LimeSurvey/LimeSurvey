<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * General helper class for generating views.
 */
class viewHelper
{

    /**
     * getImageLink returns HTML needed for a link that consists of only an image with alt text.
     *
     * Usage: getImageLink('test.png', 'controller/action/params', 'Your description', 'optionalClass', '_blank')
     *
     * @return string
     * @param string $imgName the name of the image to use, adminImageUrl will be added to it
     * @param string $linkUrl Url we want to go to, uses CController->createUrl()
     * @param string $linkTxt Text to show for the link
     * @param string $linkTarget Optional target to use for the link
     * @param string $linkClass Optional class to add to the link
     * @param array  $attribs Optional array of attirbutes to set on the link
     */
    public static function getImageLink($imgName, $linkUrl, $linkTxt, $linkTarget = null, $linkClass = 'imagelink', $attribs = array())
    {
        if (!is_null($linkUrl) && $linkUrl != '#') {
            $linkUrl = Yii::app()->getController()->createUrl($linkUrl);
        } else {
            $linkUrl = "#";
        }
        $output = '<a href="' . $linkUrl;
        if (!empty($linkClass)) {
            $output .= '" class="' . $linkClass . '"';
        }
        if (!empty($linkTarget)) {
            $output .= ' target="' . $linkTarget . '"';
        }
        if (!empty($attribs)) {
            foreach ($attribs as $attrib => $value) {
                $output .= ' ' . $attrib . '="' . str_replace('"', '&quot;', (string) $value) . '"';
            }
        }
        $output .= '><img src="' . Yii::app()->getConfig('adminimageurl') . $imgName . '" alt="' . $linkTxt . '" title="' . $linkTxt . '"></a>';

        return $output;
    }

    /**
     * getIconLink returns HTML needed for a link that consists of only an image with alt text.
     *
     * Usage: getIconLink('test.png', 'controller/action/params', 'Your description', 'optionalClass', '_blank')
     *
     * @return string
     * @param string $icoClasses the classes of the icon to generate
     * @param string $linkUrl Url we want to go to, uses CController->createUrl()
     * @param string $linkTxt Text to show for the link
     * @param string $linkTarget Optional target to use for the link
     * @param string $linkClass Optional class to add to the link
     * @param array  $attribs Optional array of attirbutes to set on the link
     */
    public static function getIconLink($icoClasses, $linkUrl, $linkTxt, $linkTarget = null, $linkClass = 'imagelink', $attribs = array())
    {
        if (!is_null($linkUrl) && $linkUrl != '#') {
            $linkUrl = Yii::app()->getController()->createUrl($linkUrl);
        } else {
            $linkUrl = "#";
        }
        $output = '<a href="' . $linkUrl;
        if (!empty($linkClass)) {
            $output .= '" class="' . $linkClass . '"';
        }
        if (!empty($linkTarget)) {
            $output .= ' target="' . $linkTarget . '"';
        }
        if (!empty($attribs)) {
            foreach ($attribs as $attrib => $value) {
                $output .= ' ' . $attrib . '="' . str_replace('"', '&quot;', (string) $value) . '"';
            }
        }
        $output .= '><span class="' . $icoClasses . '"></span></a>';

        return $output;
    }


    /**
     * getFieldText returns complete field information text.
     *
     * Usage: getFieldText($aField, $aOption)
     *
     * @return string
     * @param array $aField the field information from createFieldMap
     * @param array $aOption option (see default)
     */
    public static function getFieldText($aField, $aOption = array())
    {
        // Default options
        $aDefaultOption = array(
            'flat' => true,
            'separator' => array('(', ')'),
            'abbreviated' => false,
            'afterquestion' => " ",
            'ellipsis' => '...', // more for export or option, less for HTML display
            );
        $aOption = array_merge($aDefaultOption, $aOption);

        $sQuestionText = ""; // Always return a string
        if (isset($aField['fieldname'])) {
            $sQuestionText = self::flatEllipsizeText($aField['answertabledefinition'] ?? $aField['question'], $aOption['flat'], $aOption['abbreviated'], $aOption['ellipsis']);
            // Did this question have sub question, maybe not needed, think only isset is OK
            $bHaveSubQuestion = isset($aField['aid']) && $aField['aid'] != "";
            if ($bHaveSubQuestion) {
                $sQuestionText .= $aOption['afterquestion'];
            }
            if (isset($aField['subquestion']) && $bHaveSubQuestion) {
                $sQuestionText .= self::putSeparator(self::flatEllipsizeText($aField['subquestion'], $aOption['flat'], $aOption['abbreviated'], $aOption['ellipsis']), $aOption['separator']);
            }
            if (isset($aField['subquestion1']) && $bHaveSubQuestion) {
                $sQuestionText .= self::putSeparator(self::flatEllipsizeText($aField['subquestion1'], $aOption['flat'], $aOption['abbreviated'], $aOption['ellipsis']), $aOption['separator']);
            }
            if (isset($aField['subquestion2']) && $bHaveSubQuestion) {
                $sQuestionText .= self::putSeparator(self::flatEllipsizeText($aField['subquestion2'], $aOption['flat'], $aOption['abbreviated'], $aOption['ellipsis']), $aOption['separator']);
            }
            if (isset($aField['scale']) && $aField['scale']) {
                $sQuestionText .= self::putSeparator(self::flatEllipsizeText($aField['scale'], $aOption['flat'], $aOption['abbreviated'], $aOption['ellipsis']), $aOption['separator']);
                ;
            }
        }

        return $sQuestionText;
    }

    /**
     * getFieldCode returns complete field information code.
     *
     * Usage: getFieldCode($aField, $aOption)
     *
     * @return string
     * @param array $aField the field information from createFieldMap
     * @param array $aOption option for filtering
     */
    public static function getFieldCode($aField, $aOption = array())
    {
        // Default options
        $aDefaultOption = array(
            'LEMcompat' => false,
            'separator' => array('[', ']'),
            );
        $aOption = array_merge($aDefaultOption, $aOption);
        if ($aOption['LEMcompat']) {
            $aOption['separator'] = "_";
        }

        $sQuestionCode = "";
        if (isset($aField['fieldname'])) {
            if (isset($aField['title']) && $aField['title']) {
                $sQuestionCode = $aField['title'];
                if (isset($aField['aid']) && $aField['aid'] != "") {
                    $sQuestionCode .= self::putSeparator($aField['aid'], $aOption['separator']);
                }
                if (isset($aField['scale']) && $aField['scale']) {
                    if ($aOption['LEMcompat']) {
                        $scalenum = intval($aField['scale_id']);
                    } else {
                        $scalenum = intval($aField['scale_id']) + 1;
                    }
                    $sQuestionCode .= self::putSeparator($scalenum, $aOption['separator']);
                }
            } else {
                $sQuestionCode = $aField['fieldname'];
            }
        }

        return $sQuestionCode;
    }

    /**
     * Return a string with the good separator before and after
     *
     * @param $sString :the string
     * @param string|array : the string to put before of the array (before,after)
     * @return string
     */
    public static function putSeparator($sString, $separator)
    {
        if (is_array($separator)) {
            return $separator[0] . $sString . $separator[1];
        } else {
            return $separator . $sString;
        }
    }

    /**
     * Return a string fixed according to option
     *
     * @param string $sString :the string
     * @param boolean $bFlat : flattenText or not : completely flat (not like flattenText from common_helper)
     * @param integer $iAbbreviated : max string text (if true : always flat), 0 or false : don't abbreviated
     * @param string $sEllipsis if abbreviated : the char to put at end (or middle)
     * @param integer $fPosition if abbreviated position to split (in % : 0 to 1)
     *
     * @return string
     */
    public static function flatEllipsizeText($sString, $bFlat = true, $iAbbreviated = 0, $sEllipsis = '...', $fPosition = 1)
    {
        if ($bFlat || $iAbbreviated) {
            $sString = self::flatten($sString);
        }

        if ($iAbbreviated > 0) {
            $sString = ellipsize($sString, $iAbbreviated, $fPosition, $sEllipsis);
        }
        return $sString;
    }

    /**
     * disableLogging deactivate default logging in HTML if we don't produce HTML
     *
     * Usage: disableLogging()
     *
     * @return void
     * @author Menno Dekker
     */
    public static function disableHtmlLogging()
    {
        foreach (App()->log->routes as $route) {
            $route->enabled = $route->enabled && !($route instanceof CWebLogRoute);
        }
    }

    /**
     * Deactivate script but show it for debuging
     * This only filter script tag
     * @todo : filter inline javascript (onclick etc ..., but don't filter EM javascript)
     * Maybe doing it directly in LEM->GetLastPrettyPrintExpression();
     * @param string : Html to filter
     * @param string $sHtml
     * @return string
     * @author Denis Chenu
     */
    public static function filterScript($sHtml)
    {
        return preg_replace('#<script(.*?)>(.*?)</script>#is', '<pre>&lt;script&gt;${2}&lt;/script&gt;</pre>', $sHtml);
    }

    /**
     * Purifies HTML and returns the cleaned markup.
     *
     * Configures the purifier to allow target="_blank" and the `rel` values "external", "noreferrer", and "noopener".
     *
     * @param string $sHtml HTML to purify.
     * @return string The purified HTML string.
     */
    public static function purified($sHtml)
    {
        $oPurifier = new CHtmlPurifier();
        $oPurifier->options = array(
            'HTML.TargetBlank' => true,
            'Attr.AllowedRel' => ['external', 'noreferrer', 'noopener'],
        );
        return $oPurifier->purify($sHtml);
    }

    /**
     * return cleaned HTML
     * @param string : Html to purify
     * @return string
     */
    public static function flatten($sHtml)
    {
        $oPurifier = new CHtmlPurifier();
        $oPurifier->options = array(
            'HTML.Allowed' => '',
            'Output.Newline' => ' '
        );
        return (string) $oPurifier->purify($sHtml);
    }

    /**
     * Show clean string, leaving ONLY tag for Expression
     * @param string : Html to clean
     * @return string
     */
    public static function stripTagsEM($sHtml)
    {
        $oPurifier = new CHtmlPurifier();
        $oPurifier->options = array(
            'HTML.Allowed' => 'span[title|class],a[class|title|href]',
            'Attr.AllowedClasses' => array(
                'em-expression',
                'em-haveerror',
                'em-var-string',
                'em-function',
                'em-var-static',
                'em-var-before',
                'em-var-after',
                'em-var-inpage',
                'em-var-error',
                'em-assign',
                'em-error',
                'em-warning',
            ),
            'URI.AllowedSchemes' => array( // Maybe only local ?
                'http' => true,
                'https' => true,
                )
        );
        return $oPurifier->purify($sHtml);
    }


    /**
     * NOTE:  A real class helper is needed for twig, so I used this one for now.
     * TODO: convert surveytranslator to a real helper
     */
    public static function getLanguageData($bOrderByNative = false, $sLanguageCode = 'en')
    {
        Yii::app()->loadHelper("surveytranslator");
        return getLanguageData($bOrderByNative, $sLanguageCode);
    }

    /**
     * Get a tag to help automated tests identify pages
     * @param string $name unique view name
     * @return string
     */
    public static function getViewTestTag($name)
    {
        return sprintf('<x-test id="action::%s"></x-test>', $name);
    }
}