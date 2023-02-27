<?php

if (!defined('BASEPATH')) {
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
/**
 * Validator class for Short URLs (Survey Aliases).
 * Compares the alias against basic route rules and existing controllers, trying
 * to avoid collisions.
 */
class LSYii_ShortUrlValidator extends CValidator
{
    protected $urlManager = null;

    public function __construct($urlManager = null)
    {
        if (!isset($urlManager)) {
            $urlManager = Yii::app()->getUrlManager();
        }
        $this->urlManager = $urlManager;
    }

    protected function validateAttribute($object, $attribute)
    {
        if (empty($object->$attribute)) {
            return;
        }
        if (
            $this->matchesReservedPath($object->$attribute)
            || $this->matchesExistingRoute($object->$attribute)
            || $this->matchesExistingController($object->$attribute)
        ) {
            $this->addError($object, $attribute, gT('The survey alias matches an existing URL and cannot be used.'));
        }
    }

    /**
     * Checks whether a specified route matches a controller
     * @param string $route
     * @param CModule|null $owner
     * @return boolean
     */
    protected function matchesExistingController($route, $owner = null)
    {
        if ($owner === null) {
            $owner = Yii::app();
            $defaultController = 'surveys';
            $controllerPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'controllers';
        } else {
            $defaultController = $owner->defaultController;
            $controllerPath = $owner->getControllerPath();
        }
        if ((array)$route === $route || ($route = trim($route, '/')) === '') {
            $route = $defaultController;
        }
        $caseSensitive = Yii::app()->getUrlManager()->caseSensitive;

        $route .= '/';
        $controllerID = '';
        while (($pos=strpos($route, '/')) !== false) {
            $id = substr($route, 0, $pos);
            if (!preg_match('/^\w+$/', $id)) {
                return null;
            }
            if (!$caseSensitive) {
                $id = strtolower($id);
            }
            $route = (string)substr($route, $pos+1);
            if (!isset($basePath)) { // first segment
                if (
                    ($module = $owner->getModule($id)) !== null
                    && is_a($module, "CWebModule")
                ) {
                    return $this->isRouteValid($route, $module);
                }
                $basePath = $controllerPath;
                $controllerID = '';
            } else {
                $controllerID .= '/';
            }
            $className = ucfirst($id) . 'Controller';
            $classFile = $basePath . DIRECTORY_SEPARATOR . $className . '.php';

            if (isset($owner->controllerNamespace)) {
                $className = $owner->controllerNamespace . '\\' . str_replace('/', '\\', $controllerID) . $className;
            }

            if (is_file($classFile)) {
                if (!class_exists($className, false)) {
                    require_once($classFile);
                }
                if (class_exists($className, false) && is_subclass_of($className, 'CController')) {
                    $id[0] = strtolower($id[0]);
                    return true;
                }
                return false;
            }
            $controllerID .= $id;
            $basePath .= DIRECTORY_SEPARATOR . $id;
        }
    }

    /**
     * Checks whether a specified alias matches any of the configured routes.
     * @param string $alias
     * @return boolean
     */
    protected function matchesExistingRoute($alias)
    {
        // Since survey aliases can not contain slashes, we only care about the first part of route patterns.
        $patterns = array_keys($this->urlManager->rules);
        foreach ($patterns as $pattern) {
            // We only check against fixed routes (we can't handle routes like '<_controller:\w+>/<_action:\w+>' here)
            if (strpos($pattern, "<") === 0) {
                continue;
            }
            $firstPart = explode("/", $pattern)[0];
            if ($firstPart == $alias) {
                return true;
            }
        }
    }

    /**
     * Checks whether a specified alias matches any of the reserved words/paths.
     * @param string $alias
     * @return boolean
     */
    protected function matchesReservedPath($alias)
    {
        $reserved = [
            'assets',
            'plugins',
            'tmp',
            'upload'
        ];
        return in_array($alias, $reserved);
    }

}
