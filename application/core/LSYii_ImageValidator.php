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
    * A function to validate images,
    * This don't validate file : must validate if file exist before.
    *
    * @param array<string, string> $file Array with keys 'tmp_name' and 'type'.
    * @return array
    */
    static function validateImage(array $file)
    {
        /** @var string */
        $path = $file['tmp_name'];

        /** @var array<string, mixed> */
        $result =[];

        /** @var ?? */
        $checkImage = CFileHelper::getMimeType($path);
        $result['debug'] = $checkImage;

        // TODO: Why hard-coded?
        /** @var string[] */
        $allowedImageFormats = array(
            "image/png",
            "image/jpg",
            "image/jpeg",
            "image/gif",
            "image/svg+xml",
            "image/x-icon"
        );

        if (!empty($checkImage)
            && in_array($checkImage, $allowedImageFormats)
            && in_array(strtolower($file['type']), $allowedImageFormats)) {
            $result['uploadresult'] = '';
            $result['check'] = true;
        } else {
            $result['uploadresult'] = gT("This file is not a supported image - please only upload JPG,PNG,GIF or SVG type images.");
            $result['check'] = false;
        }
        return $result;
    }
}
