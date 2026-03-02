<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\PluginManager\PluginEvent;
use Permission;

/**
 * Handles plugin API discovery filtering and action authorization for RemoteControl.
 */
class RemoteControlPluginApiService
{
    public const CONFIG_KEY = 'rpc_plugin_api';

    /**
     * Normalize JSON-RPC payload/context into an associative array.
     *
     * @param mixed $value
     * @return array|null Null means invalid structure
     */
    public function normalizeRpcAssocArray($value): ?array
    {
        if (is_null($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            $decoded = json_decode(json_encode($value), true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /**
     * @return string|null Error status when unavailable, null when available.
     */
    public function getAvailabilityError(): ?string
    {
        $availableConfig = App()->getAvailableConfigs();
        if (!array_key_exists(self::CONFIG_KEY, $availableConfig)) {
            return 'Error: Missing configuration rpc_plugin_api';
        }
        if (!$this->isTruthyConfigValue(App()->getConfig(self::CONFIG_KEY))) {
            return 'Error: Plugin API disabled';
        }
        return null;
    }

    /**
     * Keep only actions with valid permission metadata for the current caller.
     *
     * @param array $pluginApi
     * @return array
     */
    public function filterDiscoveryForCaller(array $pluginApi): array
    {
        $filteredPluginApi = [];

        foreach ($pluginApi as $pluginClassName => $pluginDefinition) {
            if (!is_array($pluginDefinition)) {
                continue;
            }
            $actions = $pluginDefinition['actions'] ?? null;
            if (!is_array($actions)) {
                continue;
            }

            $filteredActions = [];
            foreach ($actions as $actionName => $actionDefinition) {
                if (!is_string($actionName) || !is_array($actionDefinition)) {
                    continue;
                }
                $permissionSpec = $this->extractPermissionSpec($actionDefinition);
                if ($permissionSpec === null) {
                    continue;
                }
                if ($permissionSpec['scope'] === 'global') {
                    if (!Permission::model()->hasGlobalPermission($permissionSpec['permission'], $permissionSpec['crud'])) {
                        continue;
                    }
                }
                $filteredActions[$actionName] = $actionDefinition;
            }

            if (empty($filteredActions)) {
                continue;
            }

            $pluginDefinition['actions'] = $filteredActions;
            $filteredPluginApi[$pluginClassName] = $pluginDefinition;
        }

        return $filteredPluginApi;
    }

    /**
     * Return authorization error for one plugin action call, or null when allowed.
     *
     * @param string $pluginName
     * @param string $action
     * @param array $payload
     * @param array $context
     * @return string|null
     */
    public function getActionAuthorizationError(string $pluginName, string $action, array $payload, array $context): ?string
    {
        $actionMetadata = $this->getPluginApiActionMetadata($pluginName, $action);
        if ($actionMetadata === null) {
            return 'Error: Unknown plugin API action';
        }

        $permissionSpec = $this->extractPermissionSpec($actionMetadata);
        if ($permissionSpec === null) {
            return 'Error: Invalid plugin API permission metadata';
        }

        $permissionError = null;
        if (!$this->isCallAuthorized($permissionSpec, $payload, $context, $permissionError)) {
            return $permissionError ?? 'No permission';
        }

        return null;
    }

    /**
     * Resolve one plugin action metadata for permission checks.
     *
     * @param string $pluginName
     * @param string $action
     * @return array|null
     */
    private function getPluginApiActionMetadata(string $pluginName, string $action): ?array
    {
        $event = new PluginEvent('listPluginApiActions');
        $event->set('pluginApi', []);
        $event->set('requestedPlugin', $pluginName);
        App()->getPluginManager()->dispatchEvent($event, [$pluginName]);

        $pluginApi = $event->get('pluginApi', []);
        if (!is_array($pluginApi) || !isset($pluginApi[$pluginName]) || !is_array($pluginApi[$pluginName])) {
            return null;
        }
        $actions = $pluginApi[$pluginName]['actions'] ?? null;
        if (!is_array($actions)) {
            return null;
        }
        $actionMetadata = $actions[$action] ?? null;
        return is_array($actionMetadata) ? $actionMetadata : null;
    }

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
    private function extractPermissionSpec(array $actionMetadata): ?array
    {
        $permissionMetadata = $actionMetadata['remoteControlPermission'] ?? null;
        if (is_array($permissionMetadata)) {
            $scope = strtolower(trim((string) ($permissionMetadata['scope'] ?? '')));
            $permission = trim((string) ($permissionMetadata['permission'] ?? ''));
            $crud = strtolower(trim((string) ($permissionMetadata['crud'] ?? '')));
            if (($scope !== 'global' && $scope !== 'survey') || $permission === '' || $crud === '') {
                return null;
            }

            $spec = [
                'scope' => $scope,
                'permission' => $permission,
                'crud' => $crud,
                'sid_paths' => [],
            ];

            if ($scope === 'survey') {
                $sidPaths = $permissionMetadata['sid'] ?? [];
                if (!is_array($sidPaths) || empty($sidPaths)) {
                    return null;
                }

                $normalizedSidPaths = [];
                foreach ($sidPaths as $sidPath) {
                    $sidPath = trim((string) $sidPath);
                    if ($sidPath === '') {
                        continue;
                    }
                    $normalizedSidPaths[] = $sidPath;
                }

                if (empty($normalizedSidPaths)) {
                    return null;
                }

                $spec['sid_paths'] = array_values(array_unique($normalizedSidPaths));
            }

            return $spec;
        }

        $legacyPermissions = $actionMetadata['permissions'] ?? null;
        if (is_array($legacyPermissions) && !empty($legacyPermissions)) {
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
                return [
                    'scope' => 'global',
                    'permission' => $permission,
                    'crud' => $crud,
                    'sid_paths' => [],
                ];
            }

            return [
                'scope' => 'survey',
                'permission' => $permission,
                'crud' => $crud,
                'sid_paths' => ['payload.sid', 'payload.surveyId', 'context.sid', 'context.surveyId'],
            ];
        }

        return null;
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
    private function isCallAuthorized(array $permissionSpec, array $payload, array $context, ?string &$errorStatus = null): bool
    {
        $scope = $permissionSpec['scope'] ?? '';
        $permission = $permissionSpec['permission'] ?? '';
        $crud = $permissionSpec['crud'] ?? '';
        if (!is_string($scope) || !is_string($permission) || !is_string($crud) || $scope === '' || $permission === '' || $crud === '') {
            $errorStatus = 'Error: Invalid plugin API permission metadata';
            return false;
        }

        if ($scope === 'global') {
            if (!Permission::model()->hasGlobalPermission($permission, $crud)) {
                $errorStatus = 'No permission';
                return false;
            }
            return true;
        }

        if ($scope !== 'survey') {
            $errorStatus = 'Error: Invalid plugin API permission metadata';
            return false;
        }

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
        if (!Permission::model()->hasSurveyPermission($sid, $permission, $crud)) {
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
            if (!is_array($source) || !array_key_exists($key, $source)) {
                continue;
            }
            $candidateSid = (int) $source[$key];
            if ($candidateSid > 0) {
                return $candidateSid;
            }
        }
        return 0;
    }

    /**
     * Normalize boolean-like configuration values.
     *
     * @param mixed $value
     * @return bool
     */
    private function isTruthyConfigValue($value): bool
    {
        $normalizedValue = strtolower(trim((string) $value));
        return in_array($normalizedValue, ['1', 'true', 'on', 'yes'], true);
    }
}
