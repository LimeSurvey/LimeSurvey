<?php if (!defined('BASEPATH')) {
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
        $lg['w_page'] = gT("page");

        return array('pdffont'=>$pdffont, 'pdffontsize'=>$pdffontsize, 'lg'=>$lg);
    }

}
