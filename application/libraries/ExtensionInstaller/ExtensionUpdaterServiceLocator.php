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
 * @since 2018-09-26
 * @author LimeSurvey GmbH
 */
class ExtensionUpdaterServiceLocator
{
    /**
     * @var array<string, callable>
     */
    protected $updaters = [];

    /**
     * All Yii components need an init() method.
     * @return void
     */
    public function init()
    {
        $this->addUpdaterType(
            'plugin',
            function () {
                return PluginUpdater::createUpdaters();
            }
        );

        $this->addUpdaterType(
            'theme',
            function () {
                return ThemeUpdater::createUpdaters();
            }
        );
    }

    /**
     * @param string $name Updater class name, like 'PluginUpdater', or 'ExtensionUpdater'.
     * @param callable $creator Callable that returns an ExtensionUpdater array.
     * @return void
     */
    public function addUpdaterType(string $name, callable $creator)
    {
        if (isset($this->updaters[$name])) {
            throw new \Exception("Extension installer with name $name already exists");
        }
        $this->updaters[$name] = $creator;
    }

    /**
     * Get created updaters for one updater class.
     * @param string $name
     * @return ExtensionUpdater|null
     */
    public function getUpdater(string $name)
    {
        if (isset($this->updaters[$name])) {
            $updater =  $this->updaters[$name]();
            return $updater;
        } else {
            return null;
        }
    }

    /**
     * Get all created updaters for all updater types (plugins, themes, ...).
     * @return array [ExtensionUpdater[] $updaters, string[] $errors]
     */
    public function getAllUpdaters()
    {
        // Get an extension updater for each extension installed.
        $updaters = [];
        $errors   = [];
        foreach ($this->updaters as $creator) {
            list($newUpdaters, $newErrors) = $creator();
            if ($newUpdaters) {
                $updaters = array_merge($newUpdaters, $updaters);
            }
            if ($errors) {
                $errors = array_merge($newErrors, $errors);
            }
        }
        return [$updaters, $errors];
    }
}
