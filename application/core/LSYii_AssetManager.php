<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v3 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class LSYii_AssetManager extends CAssetManager
{
    public $excludeFiles = [
        '.svn',
        '.gitignore',
        '/node_modules',
    ];
    /* @inheritdoc */
    protected function hash($path)
    {
        return sprintf(
            '%x',
            crc32(
                $path .
                App()->getConfig('versionnumber') . // Always reset with version number
                App()->getConfig('globalAssetsVersion') // Force reset between version number (for dev user)
            )
        );
    }

    /**
     * @inheritdoc
     * Announces the published directory when it is under uploaddir.
     * The default tempdir/assets location is outside uploaddir and is not announced.
     */
    public function publish($path, $hashByName = false, $level = -1, $forceCopy = null)
    {
        $published = parent::publish($path, $hashByName, $level, $forceCopy);
        if (!is_string($published)) {
            return $published;
        }
        $baseUrl = rtrim(str_replace('\\', '/', (string) $this->getBaseUrl()), '/');
        $publishedUrl = explode('?', str_replace('\\', '/', $published), 2)[0];
        if ($baseUrl === '' || !is_string($publishedUrl) || !str_starts_with($publishedUrl, $baseUrl . '/')) {
            return $published;
        }
        $suffix = substr($publishedUrl, strlen($baseUrl) + 1);
        $filesystem = $this->getBasePath() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $suffix);
        if (is_file($filesystem)) {
            $filesystem = dirname($filesystem);
        }
        Yii::import('application.helpers.common_helper', true);
        $relative = uploadRelativePath($filesystem);
        if ($relative !== null) {
            dispatchUploadPathEvent('afterUploadPathChange', $relative, ['action' => 'write']);
        }
        return $published;
    }

    /**
     * @inheritdoc
     * With db asset version used
     */
    protected function generatePath($file, $hashByName = false)
    {
        if (is_file($file)) {
            $pathForHashing = $hashByName ? dirname((string) $file) : dirname((string) $file) . "." . filemtime($file) . "." . AssetVersion::getAssetVersion($file);
        } else {
            $pathForHashing = $hashByName ? $file : $file . "." . filemtime($file) . "." . AssetVersion::getAssetVersion($file);
        }
        return $this->hash($pathForHashing);
    }
}
