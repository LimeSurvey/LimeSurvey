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

class LSYii_ImageValidator
{
    /**
    * A function to validate images
    *
    * @param mixed $path
    * @return array
    */
    static function validateImage($path)
    {
        $result =[];
        $checkImage = getimagesize($path);
        $result['debug'] = $checkImage;
        $result['uploadresult'] = '';
        $checkSvg = mime_content_type($path);

        if (!($checkImage !== false || $checkSvg === 'image/svg+xml')) {
            $result['uploadresult'] = gT("This file is not a supported image - please only upload JPG,PNG,GIF or SVG type images.");
            $result['check'] = false;
        } else {
            $result['check'] = true;
        }

        return $result;
    }

}
