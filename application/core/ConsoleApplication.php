<?php
    

    class ConsoleApplication extends CConsoleApplication
    {
        
        public $lang = null;
        
        public function __construct($config = null) {
            parent::__construct($config);
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
        public function getConfig($name)
        {
            return isset($this->$name) ? $this->$name : false;
        }

        
    }
?>
