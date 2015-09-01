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
            $identifier = $token . ' - ' . (is_null($key) ? "null" : $key);
        } else {
            $identifier = $token;
        }
        \Yii::beginProfile($identifier);

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
            $identifier = $token . ' - ' . (is_null($key) ? "null" : $key);
        } else {
            $identifier = $token;
        }

        \Yii::endProfile($identifier);

    }
}