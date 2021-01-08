<?php

interface PermissionInterface
{
    public function getOwnerId();
    public static function getPermissionData($key = null);
    public static function getMinimalPermissionRead();
    public function getCurrentPermission($sPermission, $sCRUD = 'read', $iUserID = null);
}
