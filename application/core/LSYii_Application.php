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
*/

/**
* Implements global  config
*/
class LSYii_Application extends CWebApplication
{
    protected $config = array();
    public $lang = null;

    /**
    * Initiates the application
    *
    * @access public
    * @param array $config
    * @return void
    */
    public function __construct($config = null)
    {
        if (is_string($config) && !file_exists($config))
        {
            $config = APPPATH . 'config/config-sample-mysql' . EXT;
        } 
        if(is_string($config)) {
            $config = require($config);
        }
        
        if ($config['config']['debug'] == 2)
        {
            // If debug = 2 we add firebug / console logging for all trace messages
            // If you want to var_dump $config you could do:
            // 
            // Yii::trace(CVarDumper::dumpAsString($config), 'vardump');
            // 
            // or shorter:
            // 
            //traceVar($config);
            // 
            // This statement won't cause any harm or output when debug is 1 or 0             
            $config['preload'][] = 'log';
            if (array_key_exists('components', $config) && array_key_exists('log', $config['components'])) {
                // We already have some custom logging, only add our own
            } else {
                // No logging yet, set it up
                $config['components']['log'] = array(
                    'class' => 'CLogRouter');
            }
            // Add logging of trace
            $config['components']['log']['routes'][] = array(
                'class'                      => 'CWebLogRoute', // you can include more levels separated by commas... trace is shown on debug only
                'levels'                     => 'trace',        // you can include more separated by commas
                'categories'                 => 'vardump',      // show in firebug/console
                'showInFireBug'              => true
            );
            
            // if debugsql = 1 we add sql logging to the output
            if (array_key_exists('debugsql', $config['config']) && $config['config']['debugsql'] == 1) {
                // Add logging of trace
                $config['components']['log']['routes'][] = array(
                    'class'                      => 'CWebLogRoute', // you can include more levels separated by commas... trace is shown on debug only
                    'levels'                     => 'trace',        // you can include more separated by commas
                    'categories'                 => 'system.db.*',      // show in firebug/console
                    'showInFireBug'              => true
                );
                $config['components']['db']['enableProfiling'] = true;
                $config['components']['db']['enableParamLogging'] = true;
            }
        }

        if (!isset($config['components']['request']))
        {
            $config['components']['request']=array();
        }
        $config['components']['request']=array_merge_recursive($config['components']['request'],array(
            'class'=>'LSHttpRequest',
            'noCsrfValidationRoutes'=>array(
//              '^services/wsdl.*$'   // Set here additional regex rules for routes not to be validate 
                'getTokens_json',
                'getSurveys_json',
                'remotecontrol'
            ),
            'enableCsrfValidation'=>false,    // Enable to activate CSRF protection
            'enableCookieValidation'=>false   // Enable to activate cookie protection
        ));

        parent::__construct($config);
        // Load the default and environmental settings from different files into self.
        $ls_config = require(APPPATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config-defaults.php');
        $email_config = require(APPPATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'email.php');
        $version_config = require(APPPATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'version.php');
        $settings = array_merge($ls_config, $version_config, $email_config);
        
        if(file_exists(APPPATH . DIRECTORY_SEPARATOR. 'config' . DIRECTORY_SEPARATOR . 'config.php'))
        {
            $ls_config = require(APPPATH . DIRECTORY_SEPARATOR. 'config' . DIRECTORY_SEPARATOR . 'config.php');
            if(is_array($ls_config['config']))
            {
                $settings = array_merge($settings, $ls_config['config']);
            }
        }

        foreach ($settings as $key => $value)
        {
            $this->setConfig($key, $value);
        }        
    }

    /**
    * Loads a helper
    *
    * @access public
    * @param string $helper
    * @return void
    */
    public function loadHelper($helper)
    {
        Yii::import('application.helpers.' . $helper . '_helper', true);
    }

    /**
    * Loads a library
    *
    * @access public
    * @param string $helper
    * @return void
    */
    public function loadLibrary($library)
    {
        Yii::import('application.libraries.'.$library, true);
    }

    /**
    * Sets a configuration variable into the config
    *
    * @access public
    * @param string $name
    * @param mixed $value
    * @return void
    */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
    * Loads a config from a file
    *
    * @access public
    * @param string $file
    * @return void
    */
    public function loadConfig($file)
    {
        $config = require_once(APPPATH . '/config/' . $file . '.php');
        if(is_array($config))
        {
            foreach ($config as $k => $v)
                $this->setConfig($k, $v);
        }
    }

    /**
    * Returns a config variable from the config
    *
    * @access public
    * @param string $name
    * @return mixed
    */
    public function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : false;
    }


    /**
    * For future use, cache the language app wise as well.
    *
    * @access public
    * @param Limesurvey_lang
    * @return void
    */
    public function setLang(Limesurvey_lang $lang)
    {
        $this->lang = $lang;
    }

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