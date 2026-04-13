<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

namespace LimeSurvey\ExtensionInstaller;

/**
 * @todo Survey theme, question theme, admin theme...?
 * @since 2018-10-09
 * @author LimeSurvey GmbH
 */
class ThemeUpdater extends ExtensionUpdater
{
    /**
     * Create a PluginUpdater for every plugin installed.
     * @return array [ExtensionUpdater[] $updaters, string[] $errorMessages]
     */
    public static function createUpdaters()
    {
        $themes = \Template::model()->findAll();

        $updaters = [];
        $errors   = [];
        foreach ($themes as $theme) {
            try {
                $updaters[] = new ThemeUpdater($theme);
            } catch (\Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        return [$updaters, $errors];
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->model->name;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->model->version;
    }

    /**
     * @return string
     */
    public function getExtensionType()
    {
        return 't';
    }

    /**
     * @return \ExtensionConfig
     */
    public function getExtensionConfig()
    {
        $templateDirs  = \Template::getAllTemplatesDirectories();
        $templateName  = $this->getExtensionName();
        $templateDir   = $templateDirs[$this->getExtensionName()];

        if (empty($templateDir)) {
            throw new \Exception('Found no theme dir for theme ' . $templateName);
        }

        $file = $templateDir . '/config.xml';

        if (!file_exists($file)) {
            throw new \Exception(
                sprintf(
                    'Theme %s has no config file',
                    json_encode($templateName)
                )
            );
        }

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }
        $config = simplexml_load_file(realpath($file));
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }

        return new \ExtensionConfig($config);
    }
}
