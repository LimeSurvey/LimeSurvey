<?php
/**
 * Class that saves configuration as a PHP file.
 */
class PhpConfigFile extends CComponent{
    
    protected $_config = [];
    protected $file;
    
    public function __construct($file) 
    {
        $this->file = $file;
    }
    
    public function setConfig(array $config, $merge = true) {
        $this->_config = $merge ? CMap::mergeArray($this->fileConfig, $config) : $config;
    }
    
    public function getFileConfig() {
        return file_exists($this->file) ? include($this->file) : [];
    }
    
    public function getConfig() {
        if (!isset($this->_config)) {
            $this->_config = $this->getFileConfig();
        }
        return $this->_config;
    }
    
    public function save() {
        $code = "<?php\nreturn " . var_export($this->config, true) . ';';
        return false !== file_put_contents($this->file, $code);
    }
}
