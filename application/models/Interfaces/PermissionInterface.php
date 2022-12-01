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
     * @return \CDbCriteria
     */
    public static function getPermissionCriteria();
    /**
     * @return self
     */
    public function withpermission();
}
