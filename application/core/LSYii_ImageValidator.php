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
    * @param array|string $file Either array with keys 'tmp_name', 'name' and 'type' or full file path
    * @return array
    */
    public static function validateImage($file)
    {
        if (is_array($file)) {
            $path = $file['tmp_name'];
            $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
            $type = $file['type'];
        } elseif (is_string($file)) {
            $path = $file;
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            // No MIME type is sent along with a file path, only the detected one is checked
            $type = null;
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

        /** @var string[] */
        $allowedExtensions = self::getAllowedExtensions();
        /** @var string[] */
        $allowedImageFormats = self::getAllowedMimeTypes($allowedExtensions);

        if (
            !empty($checkImage)
            && in_array($extension, $allowedExtensions)
            && in_array(strtolower((string) $checkImage), $allowedImageFormats)
            && ($type === null || in_array(strtolower((string) $type), $allowedImageFormats))
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

    /**
     * Returns the allowed image file extensions from the 'allowedthemeimageformats' setting
     *
     * @return string[] Lowercase file extensions
     */
    public static function getAllowedExtensions()
    {
        $extensions = explode(',', strtolower((string) Yii::app()->getConfig('allowedthemeimageformats')));
        return array_values(array_filter(array_map('trim', $extensions), 'strlen'));
    }

    /**
     * Returns the MIME types accepted for the given image file extensions
     *
     * The MIME types are taken from the 'imagemimetypes' setting. Extensions without
     * an entry there fall back to the MIME types of the file extension map
     * (the 'magic_file' setting or application/core/utils/fileExtensions.php).
     *
     * @param string[] $extensions Lowercase file extensions
     * @return string[] Lowercase MIME types
     */
    public static function getAllowedMimeTypes(array $extensions)
    {
        $configuredMimeTypes = (array) Yii::app()->getConfig('imagemimetypes');
        $extensionsByMimeType = null;
        $mimeTypes = [];
        foreach ($extensions as $extension) {
            if (!empty($configuredMimeTypes[$extension])) {
                $mimeTypes = array_merge($mimeTypes, (array) $configuredMimeTypes[$extension]);
                continue;
            }
            if ($extensionsByMimeType === null) {
                $magicFile = Yii::app()->getConfig('magic_file')
                    ?: Yii::getPathOfAlias('application.core.utils.fileExtensions') . '.php';
                $extensionsByMimeType = require($magicFile);
            }
            $mimeTypes = array_merge($mimeTypes, array_keys($extensionsByMimeType, $extension, true));
        }
        return array_values(array_unique(array_map('strtolower', $mimeTypes)));
    }
}
