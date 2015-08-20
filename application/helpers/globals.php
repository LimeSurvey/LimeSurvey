<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * This file contains global helper function used in LS.
     * This file MUST NOT depend on any other files, except those available in Yii
     * by default.
     *
     */

    /**
     * Helper function to replace calls to Yii::app() and enable correct code completion.
     * @return WebApplication
     */
    function App()
    {
        return Yii::app();
    }


    /**
     * If debug = 2 in application/config.php this will produce output in the console / firebug
     * similar to var_dump. It will also include the filename and line that called this method.
     *
     * @param mixed $variable The variable to be dumped
     * @param int $depth Maximum depth to go into the variable, default is 10
     */
    function traceVar($variable, $depth = 10) {
        $msg = CVarDumper::dumpAsString($variable, $depth, false);
        $fullTrace = debug_backtrace();
        $trace=array_shift($fullTrace);
        if(isset($trace['file'],$trace['line']) && strpos($trace['file'],YII_PATH)!==0)
        {
            $msg = $trace['file'].' ('.$trace['line']."):\n" . $msg;
        }
        Yii::trace($msg, 'vardump');
    }



/**
 * Helperfunction for debugging.
 */
function vd($arg) {
    if (defined('YII_DEBUG') && YII_DEBUG) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if ($trace[1]['function'] == 'vdd') {
            $details = $trace[2];
            $file = $trace[1]['file'];
            $line = $trace[1]['line'];
        } else {
            $details = $trace[1];
            $file = $trace[0]['file'];
            $line = $trace[0]['line'];

        }
        $class = \TbArray::getValue('class', $details, 'Global function');
        $token = "{$class}::{$details['function']}, ({$file}:{$line})";
        echo TbHtml::well("Dumped from: " . $token . '<br>'. CVarDumper::dumpAsString($arg, 10, true), [
            'style' => 'text-align: left;'
        ]);
    }
}

function vdd($arg) {
    vd($arg); die();
}
/**
 * Helper function for profiling.
 * @param string $key The key to be appended.
 */
function bP($key = false) {
    if (defined('YII_DEBUG') && YII_DEBUG) {
        $details = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $class = \TbArray::getValue('class', $details, 'Global function');
        $token = "{$class}::{$details['function']}";
        if ($key !== false) {
            \Yii::beginProfile($token . ' - ' . (is_null($key) ? "null" : $key));
        } else {
            \Yii::beginProfile($token);
        }
    }
}

/**
 * Helper function for profiling.
 * @param string $key The key to be appended.
 */
function eP($key = false) {
    if (defined('YII_DEBUG') && YII_DEBUG) {
        $details = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $class = \TbArray::getValue('class', $details, 'Global function');
        $token = "{$class}::{$details['function']}";
        if ($key !== false) {
            \Yii::endProfile($token . ' - ' . (is_null($key) ? "null" : $key));
        } else {
            \Yii::endProfile($token);
        }

    }
}