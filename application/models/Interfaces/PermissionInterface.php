<?php

interface PermissionInterface
{
    /**
     * Get the owner id of this record
     * @return integer|null the owner id of the object
     */
    public function getOwnerId();
    /**
     * Get Permission data for Permission object
     * @return array[]
     */
    public static function getPermissionData();
    /**
     * Get minimal access criteria : user can access to object
     * @return string|null key from permissiondata
     */
    public static function getMinimalPermissionRead();
    /**
     * @return mixed
     */
    public function getPrimaryKey();
    /**
     * Get minimal access criteria : user can access to object
     * @param integer $userid the user id for the criteria
     * @return \CDbCriteria
     */
    public static function getPermissionCriteria($userid = null);
    /**
     * Scope for access : allowed to know the object exist, and get it in list
     * @param integer $userid the user id for the criteria
     * @return self
     */
    public function withListRight($userid = null);
    /**
     * Get the permission of current model
     * @param string $sPermission Name of the permission
     * @param string $sCRUD The permission detail you want to check on:
     *  available value : 'create','read','update','delete','import' or 'export'
     * @param integer $iUserID User ID - if not given the one of the current user is used
     * @return boolean
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null);
}
