<?php
    

    class ConsoleApplication extends CConsoleApplication
    {
        
        protected $config = array();
        
        public $lang = null;
        
        public function __construct($config = null) {
            parent::__construct($config);
            // Load email settings.
            $email = require(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'email.php');
            $this->config = array_merge($this->config, $email);
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
