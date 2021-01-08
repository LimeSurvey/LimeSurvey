<?php

interface PermissionInterface
{
    public function getOwnerId();
    public static function getPermissionData();
    public static function getMinimalPermissionRead();
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null);
}
