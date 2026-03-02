<?php

namespace LimeSurvey\Models\Services;

/**
 * Parses plugin RemoteControl permission metadata into normalized specs.
 */
class RemoteControlPluginApiPermissionSpecParser
{
    /**
     * Parse permission metadata for one plugin API action.
     *
     * Supported formats:
     * - remoteControlPermission object:
     *   {scope: "global|survey", permission: "...", crud: "...", sid: ["payload.sid", ...]}
     * - legacy permissions list:
     *   ["surveycontent.read"] or ["superadmin.read"]
     *
     * @param array $actionMetadata
     * @return array|null
     */
    public function extract(array $actionMetadata): ?array
    {
        $permissionMetadata = $this->normalizeArrayLike($actionMetadata['remoteControlPermission'] ?? null);
        if (is_array($permissionMetadata)) {
            return $this->extractRemoteControlPermissionSpec($permissionMetadata);
        }

        $legacyPermissions = $this->normalizeArrayLike($actionMetadata['permissions'] ?? null);
        if (is_array($legacyPermissions) && !empty($legacyPermissions)) {
            return $this->extractLegacyPermissionSpec($legacyPermissions);
        }

        return null;
    }

    /**
     * @param array $permissionMetadata
     * @return array|null
     */
    private function extractRemoteControlPermissionSpec(array $permissionMetadata): ?array
    {
        $scope = strtolower(trim((string) ($permissionMetadata['scope'] ?? '')));
        $permission = trim((string) ($permissionMetadata['permission'] ?? ''));
        $crud = strtolower(trim((string) ($permissionMetadata['crud'] ?? '')));

        if (($scope !== 'global' && $scope !== 'survey') || $permission === '' || $crud === '') {
            return null;
        }

        if ($scope === 'global') {
            return $this->buildSpec('global', $permission, $crud, []);
        }

        $sidPaths = $this->normalizeSidPaths($permissionMetadata['sid'] ?? []);
        if (empty($sidPaths)) {
            return null;
        }

        return $this->buildSpec('survey', $permission, $crud, $sidPaths);
    }

    /**
     * @param array $legacyPermissions
     * @return array|null
     */
    private function extractLegacyPermissionSpec(array $legacyPermissions): ?array
    {
        $legacyPermission = trim((string) reset($legacyPermissions));
        if ($legacyPermission === '') {
            return null;
        }

        $permissionTokens = explode('.', $legacyPermission, 2);
        if (count($permissionTokens) !== 2) {
            return null;
        }

        $permission = trim($permissionTokens[0]);
        $crud = trim($permissionTokens[1]);
        if ($permission === '' || $crud === '') {
            return null;
        }

        if ($permission === 'superadmin') {
            return $this->buildSpec('global', $permission, $crud, []);
        }

        return $this->buildSpec('survey', $permission, $crud, [
            'payload.sid',
            'payload.surveyId',
            'context.sid',
            'context.surveyId',
        ]);
    }

    /**
     * @param mixed $sidPaths
     * @return array
     */
    private function normalizeSidPaths($sidPaths): array
    {
        if (!is_array($sidPaths) || empty($sidPaths)) {
            return [];
        }

        $normalizedSidPaths = [];
        foreach ($sidPaths as $sidPath) {
            $sidPath = trim((string) $sidPath);
            if ($sidPath !== '') {
                $normalizedSidPaths[] = $sidPath;
            }
        }

        if (empty($normalizedSidPaths)) {
            return [];
        }

        return array_values(array_unique($normalizedSidPaths));
    }

    /**
     * Normalize array-like metadata coming from plugin definitions.
     *
     * @param mixed $value
     * @return array|null
     */
    private function normalizeArrayLike($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            $normalizedValue = json_decode(json_encode($value), true);
            return is_array($normalizedValue) ? $normalizedValue : null;
        }
        return null;
    }

    /**
     * @param string $scope
     * @param string $permission
     * @param string $crud
     * @param array $sidPaths
     * @return array
     */
    private function buildSpec(string $scope, string $permission, string $crud, array $sidPaths): array
    {
        return [
            'scope' => $scope,
            'permission' => $permission,
            'crud' => $crud,
            'sid_paths' => $sidPaths,
        ];
    }
}
