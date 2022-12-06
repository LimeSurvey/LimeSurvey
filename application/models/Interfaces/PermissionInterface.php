<?php

interface PermissionInterface
{
    /**
     * @return integer|null the owner id of the pobject
     */
    public function getOwnerId();
    /**
     * @return array
     */
    public static function getPermissionData();
    /**
     * @return string|null the permission key used for read access
     */
    public static function getMinimalPermissionRead();
    /**
     * @return bool
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null);
    /**
     * @return mixed
     */
    public function getPrimaryKey();
    /**
     * @param integer $userid the user id for the criteria
     * @return \CDbCriteria
     */
    public static function getPermissionCriteria($userid = null);
    /**
     * @param integer $userid the user id for the criteria
     * @return self
     */
    public function withListRight($userid = null);
}
