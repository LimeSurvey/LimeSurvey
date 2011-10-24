<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 *
 * LS Library bootstrap file, work in progress.
 *
 * Currently requires require_once to use the library.
 */

/**
 * LS Library autoloader
 *
 * @param string $class
 */
function LS_autoload($class)
{
    $namespace = 'LS';
    $separator = '_';

    if (0 !== strpos($class, $namespace.$separator))
    {
        return;
    }

    $translated = str_replace($separator, DIRECTORY_SEPARATOR, $class);
    $libpath = dirname(__FILE__).DIRECTORY_SEPARATOR.'src';
    $file = $libpath.DIRECTORY_SEPARATOR.$translated.'.php';

    require $file; # provoke fatal error if file does not exists.
}
 
spl_autoload_register('LS_autoload');
