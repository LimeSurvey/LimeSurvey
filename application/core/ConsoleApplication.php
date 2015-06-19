<?php

    /**
     * Load the globals helper as early as possible. Only earlier solution is to use
     * index.php
     */
    require_once(dirname(dirname(__FILE__)) . '/helpers/globals.php');

    class ConsoleApplication extends CConsoleApplication
    {
        protected $config = array();
        public $installed;

        /**
         *
         * @var \ls\pluginmanager\PluginManager
         */
        protected $pluginManager;

        /**
         * @var LimesurveyApi
         */
        protected $api;

        public function getIsInstalled() {
            $components = $this->getComponents(false);
            return is_object($components['db'])
            || isset($components['db']['connectionString']);
        }
        public function __construct($config = null) {
            $this->installed = isset($config['components']['db']['connectionString']);
            // Silent fail on unknown configuration keys.
            foreach($config as $key => $value) {
                if (!property_exists(__CLASS__, $key) && !$this->hasProperty($key)) {
                    unset($config[$key]);
                }
            }
            parent::__construct($config);
            Yii::import('application.helpers.common_helper', true);
        }
        
        public function init() {
            parent::init();
            $this->commandRunner->addCommands(Yii::getFrameworkPath() . '/cli/commands');
            foreach ($this->commandRunner->commands as $command => &$config) {
                $config = [
                    'class' => "ls\\cli\\" . ucfirst($command) . "Command"
                ];
            }
            
            // Set webroot alias.
            Yii::setPathOfAlias('webroot', realpath(Yii::getPathOfAlias('application') . '/../'));
            // Load email settings.
            $email = require(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'email.php');
            $this->config = array_merge($this->config, $email);

            $this->name = SettingGlobal::get('sitename', 'LimeSurvey');
        }

        /**
         * This function is implemented since em_core_manager incorrectly requires
         * it to create urls.
         */
        public function getController()
        {
            return $this;
        }


        /**
        * Returns a config variable from the config
        *
        * @access public
        * @param string $name
        * @return mixed
        */
        public function getConfig($name = null)
        {
            if (isset($this->$name))
            {
                return $this->name;
            }
            elseif (isset($this->config[$name]))
            {
                return $this->config[$name];
            }
            else
            {
                return false;
            }
        }

        /**
         * Get the pluginManager
         *
         * @return PluginManager
         */
        public function getPluginManager()
        {
            return $this->getComponent('PluginManager');
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

    }
?>
