<?php
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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
 * Exception Handler as a Hook
 *
 * @author mot
 */
class ExceptionHook
{
    private $previousHandler;

    public function SetExceptionHandler()
    {
        $this->previousHandler = set_exception_handler(array($this, 'ExceptionHandler'));
    }

    public function ExceptionHandler(Exception $exception)
    {
        $class = get_class($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        $error = sprintf("Uncaught exception '%s' with message '%s'", $class, $message);

        // give CI a chance to handle the error (display message, logging)
        _exception_handler(E_ERROR, $error, $file, $line);

        // bubble up if necessary
        if ($this->previousHandler)
            $this->previousHandler($exception);

        // let PHP handle the rest.
        throw $exception;
    }
}
