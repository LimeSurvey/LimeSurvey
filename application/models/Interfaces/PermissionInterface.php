<?php

interface PermissionInterface
{
    public function getOwnerId();
    /**
     * @return array
     */
    public static function getPermissionData();
    public static function getMinimalPermissionRead();
    /**
     * @return bool
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null);
    public function getPrimaryKey();
}
