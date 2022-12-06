<?php

trait PermissionTrait
{
    /**
     * Get the owner id of this record
     * Used for Permission, to be extendable for each model with owner
     * @return integer|null
     */
    public function getOwnerId()
    {
        return null;
    }

    /**
     * Get Permission data for Permission object
     * @param string $key
     * @return array
     */
    public static function getPermissionData()
    {
        return array();
    }

    /**
     * Get minimal permission name (for read value)
     * @return null|string
     */
    public static function getMinimalPermissionRead()
    {
        return null;
    }

    /**
     * Get minimal access criteria : user can access to object
     * Criteria used for list or grid
     * Avoid usage of Permission on each object returned when there are a lot.
     * @see self::withListRight for adding it at scope in find action.
     * @param integer $userid
     * @return CDbCriteria
     */
    public static function getPermissionCriteria($userid = null)
    {
        $criteriaPerm = new CDbCriteria();
        return $criteriaPerm;
    }

    /**
     * Scope for minimal access : allowed to know the object exist
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
     * Get the permission of current model
     * @param string $sPermission Name of the permission
     * @param string $sCRUD The permission detail you want to check on: 'create','read','update','delete','import' or 'export'
     * @param integer $iUserID User ID - if not given the one of the current user is used
     * @return boolean
     */
    public function hasPermission(/** @scrutinizer ignore-unused */ $sPermission, $sCRUD = 'read', $iUserID = null)
    {
        if (empty($iUserID)) {
            $iUserID = \Permission::model()->getUserId();
        }
        if (\Permission::model()->hasGlobalPermission('superadmin', $sCRUD, $iUserID)) {
            return true;
        }
        /* No default global : adding it ? */
        return false;
    }
}
