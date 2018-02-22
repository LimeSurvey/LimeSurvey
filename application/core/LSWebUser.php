<?php
    Yii::import('application.helpers.Hash', true);

    /**
     * @property integer $id The user ID
     */
    class LSWebUser extends CWebUser
    {
        protected $sessionVariable = 'LSWebUser';


        public function __construct()
        {
            $this->loginUrl = Yii::app()->createUrl('admin/authentication', array('sa' => 'login'));
        }

        public function checkAccess($operation, $params = array(), $allowCaching = true)
        {
            if ($operation == 'administrator') {
                return Permission::model()->hasGlobalPermission('superadmin', 'read');
            } else {
                return parent::checkAccess($operation, $params, $allowCaching);
            }

        }

        public function getStateKeyPrefix()
        {
            return $this->sessionVariable;
        }


        public function setFlash($key, $value, $defaultValue = null)
        {
            $this->setState("flash.$key", $value, $defaultValue);
        }
        public function hasFlash($key)
        {
            $this->hasState("flash.$key");
        }

        public function getFlashes($delete = true)
        {
            $result = $this->getState('flash', array());
            $this->removeState('flash');
            return $result;
        }

        /**
         * @param string $key
         * @param mixed $defaultValue
         * @return mixed|null
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
            $current = isset($_SESSION[$this->sessionVariable]) ? $_SESSION[$this->sessionVariable] : array();
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

    }
