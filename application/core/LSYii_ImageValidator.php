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

class LSYii_ImageValidator
{
    /**
    * A function to validate images,
    * This don't validate file : must validate if file exist before.
    *
    * @param array|string $file Either array with keys 'tmp_name' and 'type' or full file path
    * @return array
    */
    public static function validateImage($file)
    {
        if (is_array($file)) {
            $path = $file['tmp_name'];
            $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            $type = $file['type'];
        } elseif (is_string($file)) {
            $parts = explode('.', $file);
            $path = $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $type = 'image/' . $extension;
        } else {
            return [
                // No translation ? send $file ?
                'uploadresult' => 'Internal error: $file is not array or string',
                'check' => false
            ];
        }

        /** @var array<string, mixed> */
        $result = [];

        /** @var ?? */
        $checkImage = CFileHelper::getMimeType($path, null, false); // Don't fallback to checking the file extension because the tmp file name doesn't have one.
        $result['debug'] = $checkImage;

        // TODO: Why hard-coded?
        /** @var string[] */
        $allowedImageFormats = array(
            "image/png",
            "image/jpg",
            "image/jpeg",
            "image/ico",
            "image/gif",
            "image/svg+xml",
            "image/svg",
            "image/x-icon",
            "image/vnd.microsoft.icon"
        );

        if (
            !empty($checkImage)
            && in_array($extension, explode(",", (string) Yii::app()->getConfig('allowedthemeimageformats')))
            && in_array($checkImage, $allowedImageFormats)
            && in_array(strtolower((string) $type), $allowedImageFormats)
        ) {
            $result['uploadresult'] = '';
            $result['check'] = true;
        } else {
            // If $checkImage is empty, it's most probably because fileinfo is missing. But we check it to be sure.
            if (is_null($checkImage) && !function_exists('finfo_open')) {
                $result['uploadresult'] = gT("Fileinfo PHP extension is not installed. Couldn't validate the image format of the file.");
                $result['check'] = false;
            } else {
                $result['uploadresult'] = sprintf(gT("This file is not a supported image format - only the following ones are allowed: %s"), strtoupper((string) Yii::app()->getConfig('allowedthemeimageformats')));
                $result['check'] = false;
            }
        }
        return $result;
    }
}
