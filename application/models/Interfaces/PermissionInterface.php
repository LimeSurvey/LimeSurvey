<?php

interface PermissionInterface
{
    /**
     * these are the single permissions that could be set to true or false in db
     * for a permission like 'survey'
     */
    public const SINGLE_PERMISSIONS =
        [
        'create',
        'read',
        'update',
        'delete',
        'import',
        'export'
        ];

    /**
     * @return integer|null
     */
    public function getOwnerId();
    /**
     * @return array
     */
    public static function getPermissionData();
    /**
     * @return null|string
     */
    public static function getMinimalPermissionRead();
    /**
     * @return bool
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null);
    /**
     * @return integer|array
     */
    public function getPrimaryKey();
}
