<?php
namespace ls\pluginmanager;

abstract class PluginModule extends \CWebModule implements iPlugin
{
    public $dir;
    public $namespace;
    private $_description;
    
    public function getDescription() {
        return $this->_description;
    }
    
    public function setDescription($value) {
        $this->_description = $value;
    }
    /**
     * Discard the name value.
     * @param string $value
     */
    public function setName($value) {
        
    }
    public function getEvent() {
        
    }

    public function getPluginSettings($getValues = true) {
        
    }

    public function getStore() {
        
    }

    public function saveSettings($aSettings) {
        
    }

    public function setEvent(PluginEvent $event) {
        
    }


}
