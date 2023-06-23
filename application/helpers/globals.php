<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
    /**
     * This file contains global helper function used in LS.
     * This file MUST NOT depend on any other files, except those available in Yii
     * by default.
     *
     */

    /**
     * Helper function to replace calls to Yii::app() and enable correct code completion.
     * @return LSYii_Application
     */
function App()
{
    /** @var LSYii_Application $app */
    $app = Yii::app();
    return $app;
}

    /**
     * If debug = 2 in application/config.php this will produce output in the console / firebug
     * similar to var_dump. It will also include the filename and line that called this method.
     *
     * @param mixed $variable The variable to be dumped
     * @param int $depth Maximum depth to go into the variable, default is 10
     */
function traceVar($variable, $depth = 10)
{
    $msg = CVarDumper::dumpAsString($variable, $depth, false);
    $fullTrace = debug_backtrace();
    $trace = array_shift($fullTrace);
    if (isset($trace['file'], $trace['line']) && strpos($trace['file'], (string) YII_PATH) !== 0) {
        $msg = $trace['file'] . ' (' . $trace['line'] . "):\n" . $msg;
    }
    Yii::log($msg, 'trace', 'vardump');
}
