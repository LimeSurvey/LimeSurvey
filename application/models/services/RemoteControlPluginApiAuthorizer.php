<?php

namespace LimeSurvey\Models\Services;

use Permission;

/**
 * Applies normalized permission specs to discovery and invocation checks.
 */
class RemoteControlPluginApiAuthorizer
{
    /**
     * @param array $permissionSpec
     * @return bool
     */
    public function isDiscoveryAllowed(array $permissionSpec): bool
    {
        $scope = $permissionSpec['scope'] ?? '';
        if ($scope !== 'global') {
            return true;
        }

        $permission = $permissionSpec['permission'] ?? '';
        $crud = $permissionSpec['crud'] ?? '';
        return is_string($permission)
            && is_string($crud)
            && $permission !== ''
            && $crud !== ''
            && Permission::model()->hasGlobalPermission($permission, $crud);
    }

    /**
     * Authorize one plugin API action invocation.
     *
     * @param array $permissionSpec
     * @param array $payload
     * @param array $context
     * @param string|null $errorStatus
     * @return bool
     */
    public function isCallAuthorized(array $permissionSpec, array $payload, array $context, ?string &$errorStatus = null): bool
    {
        if (!$this->isValidPermissionSpec($permissionSpec)) {
            $errorStatus = 'Error: Invalid plugin API permission metadata';
            return false;
        }

        if ($permissionSpec['scope'] === 'global') {
            return $this->authorizeGlobal($permissionSpec['permission'], $permissionSpec['crud'], $errorStatus);
        }

        if ($permissionSpec['scope'] !== 'survey') {
            $errorStatus = 'Error: Invalid plugin API permission metadata';
            return false;
        }

        return $this->authorizeSurvey($permissionSpec, $payload, $context, $errorStatus);
    }

    /**
     * @param array $permissionSpec
     * @return bool
     */
    private function isValidPermissionSpec(array $permissionSpec): bool
    {
        $scope = $permissionSpec['scope'] ?? '';
        $permission = $permissionSpec['permission'] ?? '';
        $crud = $permissionSpec['crud'] ?? '';

        return is_string($scope)
            && is_string($permission)
            && is_string($crud)
            && $scope !== ''
            && $permission !== ''
            && $crud !== '';
    }

    /**
     * @param string $permission
     * @param string $crud
     * @param string|null $errorStatus
     * @return bool
     */
    private function authorizeGlobal(string $permission, string $crud, ?string &$errorStatus): bool
    {
        if (!Permission::model()->hasGlobalPermission($permission, $crud)) {
            $errorStatus = 'No permission';
            return false;
        }

        return true;
    }

    /**
     * @param array $permissionSpec
     * @param array $payload
     * @param array $context
     * @param string|null $errorStatus
     * @return bool
     */
    private function authorizeSurvey(array $permissionSpec, array $payload, array $context, ?string &$errorStatus): bool
    {
        $sidPaths = $permissionSpec['sid_paths'] ?? [];
        if (!is_array($sidPaths) || empty($sidPaths)) {
            $errorStatus = 'Error: Invalid plugin API permission metadata';
            return false;
        }

        $sid = $this->resolveSurveyIdFromPaths($sidPaths, $payload, $context);
        if ($sid <= 0) {
            $errorStatus = 'Faulty parameters: payload.sid is required for permission check';
            return false;
        }

        if (!Permission::model()->hasSurveyPermission($sid, $permissionSpec['permission'], $permissionSpec['crud'])) {
            $errorStatus = 'No permission';
            return false;
        }

        return true;
    }

    /**
     * Resolve survey ID from path expressions like payload.sid or context.surveyId.
     *
     * @param array $sidPaths
     * @param array $payload
     * @param array $context
     * @return int
     */
    private function resolveSurveyIdFromPaths(array $sidPaths, array $payload, array $context): int
    {
        foreach ($sidPaths as $sidPath) {
            $sidPath = trim((string) $sidPath);
            if ($sidPath === '') {
                continue;
            }

            $segments = explode('.', $sidPath, 2);
            if (count($segments) !== 2) {
                continue;
            }

            $root = $segments[0];
            $key = $segments[1];
            if ($root !== 'payload' && $root !== 'context') {
                continue;
            }

            $source = $root === 'payload' ? $payload : $context;
            if (!array_key_exists($key, $source)) {
                continue;
            }

            $candidateSid = (int) $source[$key];
            if ($candidateSid > 0) {
                return $candidateSid;
            }
        }

        return 0;
    }
}
