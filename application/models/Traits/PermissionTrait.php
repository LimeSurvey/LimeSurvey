<?php

trait PermissionTrait
{
    /**
     * @inheritdoc
     * Used for Permission, to be extendable for each model with owner
     */
    public function getOwnerId()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function getPermissionData()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public static function getMinimalPermissionRead()
    {
        return null;
    }

    /**
     * @inheritdoc
     * Criteria used for list or grid
     * Avoid usage of Permission on each object returned when there are a lot.
     * @see self::withListRight for adding it at scope in find action.
     * @param integer $userid (use current if is null)
     * @return CDbCriteria
     */
    public static function getPermissionCriteria($userid = null)
    {
        if (empty($userid)) {
            $userid = App()->user->id;
        }
        /* Remind to set $userid if is null to current : Yii::app()->user->id; */
        $criteriaPerm = new CDbCriteria();
        /* New criteria to be added if user didn't have global read permission on this object */
        /* UserId can be used with (for example) $criteriaPerm->compare('t.owner_id', $userid, false); */
        /* If object have minimal permission (set $this->getMinimalPermissionRead()) */
        /* can be added with join request and  read_p = 1*/
        /* etc */
        return $criteriaPerm;
    }

    /**
     * @inheritdoc
     * Uses for scope in list and search
     * This don't mean user are allowed to read all information or any other Permission
     * @param integer $userid
     * @return self
     */
    public function withListRight($userid = null)
    {
        $this->getDbCriteria()->mergeWith(self::getPermissionCriteria($userid));
        return $this;
    }

    /**
     * @inheritdoc
     * Set $iUserID to current user if null
     * Check if have whole permissio
     * Check if have DB (or plugin permission)
     */
    public function hasPermission(/** @scrutinizer ignore-unused */ $sPermission, $sCRUD = 'read', $iUserID = null)
    {
        if (empty($iUserID)) {
            $iUserID = App()->user->id;
        }
        if ($this->haveAllPermission($iUserID)) {
            return true;
        }
        return $this->haveDbPermission($sPermission, $sCRUD, $iUserID);
    }

    /**
     * Check if user have whole permission by core LimeSurvey system
     * @param integer User ID
     * @return boolean
     */
    public function haveAllPermission($iUserID)
    {
        /* User is superadmin */
        if (\Permission::model()->hasGlobalPermission('superadmin', $sCRUD, $iUserID)) {
            return true;
        }
        /* User is owner */
        if ($this->getOwnerId() && $iUserID === $this->getOwnerId()) {
            return true;
        }
        return false;
    }

    /**
     * Check if user have DB or plugin permission
     * always false if $sPermission is not in \Permission::getGlobalPermissionData (except by plugin)
     * @param integer User ID
     * @return boolean
     */
    public function haveDbPermission($sPermission, $sCRUD, $iUserID)
    {
        return \Permission::model()->hasGlobalPermission($sPermission, $sCRUD, $iUserID);
    }
}
