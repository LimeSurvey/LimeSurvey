<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
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
 * General helper class for generating pdf.
 */
class pdfHelper
{
    /**
     * getPdfLanguageSettings
     *
     * Usage: getPdfLanguageSettings($language)
     *
     * @return array ('pdffont','pdffontsize','lg'=>array('a_meta_charset','a_meta_dir','a_meta_language','w_page')
     * @param string $language : language code for the PDF
     */
    public static function getPdfLanguageSettings($language)
    {
        Yii::import('application.libraries.admin.pdf', true);
        Yii::import('application.helpers.surveytranslator_helper', true);

        $pdffont = Yii::app()->getConfig('pdfdefaultfont');
        if ($pdffont == 'auto') {
            $pdffont = PDF_FONT_NAME_DATA;
        }
        $pdfcorefont = array("freesans", "dejavusans", "courier", "helvetica", "freemono", "symbol", "times", "zapfdingbats");
        if (in_array($pdffont, $pdfcorefont)) {
            $alternatepdffontfile = Yii::app()->getConfig('alternatepdffontfile');
            if (array_key_exists($language, $alternatepdffontfile)) {
                $pdffont = $alternatepdffontfile[$language]; // Actually use only core font
            }
        }
        $pdffontsize = Yii::app()->getConfig('pdffontsize');
        if ($pdffontsize == 'auto') {
            $pdffontsize = PDF_FONT_SIZE_MAIN;
        }
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        if (getLanguageRTL($language)) {
            $lg['a_meta_dir'] = 'rtl';
        } else {
            $lg['a_meta_dir'] = 'ltr';
        }
        $lg['a_meta_language'] = $language;
        $lg['w_page'] = gT("Page");

        return array('pdffont' => $pdffont, 'pdffontsize' => $pdffontsize, 'lg' => $lg);
    }

    /**
     * Removes the bold and italic styles a PDF font can not print in a language.
     *
     * Some fonts contain the letters of a language only in their regular file.
     * Bold or italic text would be printed as empty boxes, so the styles listed
     * in $config['pdfunsupportedfontstyles'] for the font and language are dropped.
     *
     * @param string|null $language Language code of the PDF
     * @param string|null $family Font family the style is requested for
     * @param string|null $style Font style as used by TCPDF, for example "B", "BI" or "BU"
     * @return string The font style without the styles the font can not print
     */
    public static function removeUnsupportedFontStyles($language, $family, $style)
    {
        $style = (string) $style;
        if ($style === '' || empty($language) || empty($family)) {
            return $style;
        }
        $unsupportedStyles = (array) Yii::app()->getConfig('pdfunsupportedfontstyles');
        $fontStyles = $unsupportedStyles[strtolower((string) $family)][$language] ?? '';
        if (!is_string($fontStyles) || $fontStyles === '') {
            return $style;
        }
        return str_ireplace(str_split($fontStyles), '', $style);
    }
}
