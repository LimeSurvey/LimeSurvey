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

/**
 * Requires a file in a separate context.
 * - Prevents namespace pollution
 * @param [] $context The context to pass to require.
 * @param string $file The file name to require
 * @return array The result of requiring the php file.
 */
function require_config(array $context = []) {
    extract($context);
    if (!isset($context['context'])) {
        unset($context);
    }
    // We must use func_get_arg to guarantee a clean environment (ie no variables defined).
    $result = require func_get_arg(1);
    if (!is_array($result)) {
        throw new \Exception("Config files must return an array when included.");
    }
    return $result;
}