<?php
namespace ls\components;

use ls\pluginmanager\AuthenticationPluginInterface;
use ls\pluginmanager\iUser;

/**
 * @property-read iUser $model;
 */
class WebUser extends \CWebUser
{
    private $_access = [];
    protected $_authManager;

    /**
     * Returns the plugin responsible for authenticating the current user.
     * @return AuthenticationPluginInterface
     */
    public function getPlugin()
    {
        return App()->pluginManager->getPlugin($this->getState('authenticationPlugin'));
    }

    public function getModel()
    {
        return $this->getPlugin()->getUser($this->id);
    }

    public function getAuthManager()
    {
        if (!isset($this->_authManager)) {
            $this->_authManager = App()->authManager;
        }

        return $this->_authManager;
    }

    public function setAuthManager(IAuthManager $value)
    {
        $this->_authManager = $value;
    }

    /**
     * This implementation supports using a different auth manager.
     * @param type $operation
     * @param type $params
     * @param type $allowCaching
     * @return type
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        if ($allowCaching && $params === array() && isset($this->_access[$operation])) {
            return $this->_access[$operation];
        }


        $access = $this->getAuthManager()->checkAccess($operation, $this->getId(), $params);
        if ($allowCaching && $params === array()) {
            $this->_access[$operation] = $access;
        }

        return $access;
    }

}
