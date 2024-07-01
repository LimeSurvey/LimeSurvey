<?php

/**
 * @inheritdoc
 */
class LSWebUser extends CWebUser
{
    protected $sessionVariable = 'LSWebUser';

    public function __construct()
    {
        Yii::import('application.helpers.Hash', true);
        $this->loginUrl = Yii::app()->createUrl('admin/authentication', array('sa' => 'login'));
    }

    /**
     * @inheritDoc
     * Replace auto getter to check if current user is valid or not
     */
    public function getId()
    {
        if (empty(parent::getId())) {
            return parent::getId();
        }
        $id = App()->getCurrentUserId();
        if ($id === 0) {
            /* User is still connected but invalid : logout */
            $this->logout();
        }
        return $id;
    }

    /**
     * @inheritDoc
     * Set id in session too
     */
    public function setId($id)
    {
        parent::setId($id);
        \Yii::app()->session['loginID'] = $id;
    }

    /**
     * @inheritDoc
     * Add the specific plugin event and regenerate CSRF
     */
    public function logout($destroySession = true)
    {
        /* Adding beforeLogout event */
        $beforeLogout = new PluginEvent('beforeLogout');
        App()->getPluginManager()->dispatchEvent($beforeLogout);
        regenerateCSRFToken();
        parent::logout($destroySession);
        /* Adding afterLogout event */
        $event = new PluginEvent('afterLogout');
        App()->getPluginManager()->dispatchEvent($event);
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        if ($operation == 'administrator') {
            return Permission::model()->hasGlobalPermission('superadmin', 'read');
        } else {
            return parent::checkAccess($operation, $params, $allowCaching);
        }
    }

    /**
     * @inheritdoc
     * replace by a fixed string
     */
    public function getStateKeyPrefix()
    {
        return $this->sessionVariable;
    }

    /**
     * @inheritdoc
     */
    public function setFlash($key, $value, $defaultValue = null)
    {
        $this->setState("flash.$key", $value, $defaultValue);
    }

    /**
     * @inheritdoc
     */
    public function hasFlash($key)
    {
        $this->hasState("flash.$key");
    }

    /**
     * Replace default system to return only one flash …
     */
    public function getFlashes($delete = true)
    {
        $result = $this->getState('flash', array());
        $this->removeState('flash');
        return $result;
    }

    /**
     * @inheritdoc
     * replace session variable
     */
    public function getState($key, $defaultValue = null)
    {
        if (!isset($_SESSION[$this->sessionVariable]) || !Hash::check($_SESSION[$this->sessionVariable], $key)) {
            return $defaultValue;
        } else {
            return Hash::get($_SESSION[$this->sessionVariable], $key);
        }
    }

    /**
     * Removes a state variable.
     * @param string $key
     */
    public function removeState($key)
    {
        $this->setState($key, null);
    }

    public function setState($key, $value, $defaultValue = null)
    {
        $current = $_SESSION[$this->sessionVariable] ?? array();
        if ($value === $defaultValue) {
            $_SESSION[$this->sessionVariable] = Hash::remove($current, $key);
        } else {
            $_SESSION[$this->sessionVariable] = Hash::insert($current, $key, $value);
        }
    }

    public function hasState($key)
    {
        return isset($_SESSION[$this->sessionVariable]) && Hash::check($_SESSION[$this->sessionVariable], $key);
    }

    /**
     * Test if a user is in a group
     * @param int $gid
     * @return boolean
     */
    public function isInUserGroup($gid)
    {
        $oUsergroup = UserGroup::model()->findByPk($gid);

        // The group doesn't exist anymore
        if (!is_object($oUsergroup)) {
            return false;
        }

        $users = $oUsergroup->users;
        $aUids = array();
        foreach ($users as $user) {
            $aUids[] = $user->uid;
        }

        if (in_array($this->id, $aUids)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if user have xss allowed
     * @return boolean
     */
    public function isXssFiltered()
    {
        if (Yii::app()->getConfig('DBVersion') < 172) {
            // Permission::model exist only after 172 DB version
            return Yii::app()->getConfig('filterxsshtml');
        }
        if (Yii::app()->getConfig('filterxsshtml')) {
            return !\Permission::model()->hasGlobalPermission('superadmin', 'read');
        }
        return false;
    }

    /**
     * Check if user is allowed to edit script
     * @return boolean
     */
    public function isScriptUpdateAllowed()
    {
        if (!Yii::app()->getConfig('disablescriptwithxss')) {
            return true;
        }
        return !$this->isXssFiltered();
    }
}
