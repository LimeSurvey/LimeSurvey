<?php

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
final class SettingsStorage extends ArrayObject
{
    protected static $_instance = null;

    public function __construct($params = array())
    {
        $defaults = array('array' => array(), 'flags' => parent::ARRAY_AS_PROPS);

        foreach ($defaults as $key => $val) {
            if (isset($params[$key]) && $params[$key] !== "") {
                $defaults[$key] = $params[$key];
            }
        }
        extract($defaults);

        parent::__construct($array, $flags);
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new Exception("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    public static function isRegistered($index)
    {
        if (self::$_instance === null) {
            return false;
        }
        return self::$_instance->offsetExists($index);
    }
}
