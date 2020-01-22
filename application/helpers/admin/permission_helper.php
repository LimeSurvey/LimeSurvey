<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Checks Permission for the current user and returns an array with Permissions
 *
 * @param array $globalPermissions
 * @param array $customPermissions
 *
 * @return array
 */
function permissionsAsArray($globalPermissions, $customPermissions = [])
{
    $permissionsArray = [];
    foreach ($globalPermissions as $permission => $allCrud) {
        foreach ($allCrud as $crud) {
            $permissionsArray[$permission][$crud] = Permission::model()->hasGlobalPermission($permission, $crud);
        }
    }
    if ($customPermissions) {
        foreach ($customPermissions as $permission => $customPermission) {
            foreach ($customPermission as $type => $access) {
                if (in_array($type, ['create', 'read', 'update', 'delete', 'import', 'export'])) {
                    break;
                }
                $permissionsArray[$permission][$type] = $access;
            }
        }
    }
    return $permissionsArray;
}
