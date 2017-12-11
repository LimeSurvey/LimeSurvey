<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

class LSYii_Locale extends CLocale
{
    /**
     * Converts a locale ID to its canonical form.
     * In canonical form, a locale ID consists of only underscores and lower-case letters.
     * @param string $id the locale ID to be converted
     * @return Clocale The locale ID in canonical form
     */
    public static function getInstance($id)
    {
        // Fix up the LimeSurvey language code for Yii
        $aLanguageData = getLanguageData();
        if (isset($aLanguageData[$id]['cldr'])) {
            $id = $aLanguageData[$id]['cldr'];
        }
        static $locales = array();
        if (isset($locales[$id])) {
                    return $locales[$id];
        } else {
                    return $locales[$id] = new CLocale($id);
        }
    }

}