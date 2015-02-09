<?php

/**
 * @property \ls\pluginmanager\PluginBase $plugin
 */
class PluginIdentity extends CBaseUserIdentity {
    /**
     *
     * @var \ls\pluginmanager\AuthPluginBase
     */
    private $plugin;
    protected $_id;
    protected $_name;
    public function __construct(\ls\pluginmanager\AuthPluginBase $plugin) {
        $this->plugin = $plugin;
    }
    public function authenticate() {
        $result = $this->plugin->authenticate(Yii::app()->request);
        $this->setState('authenticationPlugin', $this->plugin->id);
        if (!isset($result)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->errorCode = self::ERROR_NONE;
            if (is_array($result)) {
                $this->_name = $result['name'];
                $this->_id = $result['id'];
                unset($result['name']);
                unset($result['id']);
                $this->attributes = $result;
            }
        }
        return $this->errorCode === self::ERROR_NONE;
    }
    
    public function getId() {
        return $this->_id;
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function setAttributes($value) {
        return $this->setState('attributes', $value);
    }
    public function getAttributes() {
        return $this->getState('attributes', []);
    }

    
    
    

}