<?php

/**
 * @property \ls\pluginmanager\PluginBase $plugin
 */
class PluginIdentity extends CBaseUserIdentity {
    /**
     *
     * @var \ls\pluginmanager\iAuthenticationPlugin
     */
    private $plugin;
    
    public function __construct(\ls\pluginmanager\iAuthenticationPlugin $plugin) {
        $this->plugin = $plugin;
    }
    public function authenticate() {
        $result = $this->plugin->authenticate(Yii::app()->request);
        $this->setState('authenticationPlugin', $this->plugin->id);
        if (!$result instanceof ls\pluginmanager\iUser) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else {
            $this->errorCode = self::ERROR_NONE;
            $this->model = $result;
        }
        return $this->errorCode === self::ERROR_NONE;
    }
    
    public function getId() {
        return $this->model->getId();
    }
    
    public function getName() {
        return $this->model->getName();
    }
    
    public function setModel($value) {
        return $this->setState('model', $value);
    }
    public function getModel() {
        return $this->getState('model', []);
    }

    
    
    

}