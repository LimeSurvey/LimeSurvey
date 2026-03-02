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

    /** @var RemoteControlPluginApiPermissionSpecParser */
    private $permissionSpecParser;

    /** @var RemoteControlPluginApiAuthorizer */
    private $authorizer;

    public function __construct(
        ?RemoteControlPluginApiPermissionSpecParser $permissionSpecParser = null,
        ?RemoteControlPluginApiAuthorizer $authorizer = null
    ) {
        $this->permissionSpecParser = $permissionSpecParser ?? new RemoteControlPluginApiPermissionSpecParser();
        $this->authorizer = $authorizer ?? new RemoteControlPluginApiAuthorizer();
    }

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
            if ($this->isSequentialArray($value)) {
                return null;
            }
            return $value;
        }
        if (is_object($value)) {
            $decoded = json_decode(json_encode($value), true);
            if (!is_array($decoded) || $this->isSequentialArray($decoded)) {
                return null;
            }
            return $decoded;
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

            $filteredActions = $this->filterAllowedActions($actions);
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

        $permissionSpec = $this->permissionSpecParser->extract($actionMetadata);
        if ($permissionSpec === null) {
            return 'Error: Invalid plugin API permission metadata';
        }

        $permissionError = null;
        if (!$this->authorizer->isCallAuthorized($permissionSpec, $payload, $context, $permissionError)) {
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
     * @param array $actions
     * @return array
     */
    private function filterAllowedActions(array $actions): array
    {
        $filteredActions = [];

        foreach ($actions as $actionName => $actionDefinition) {
            if (!is_string($actionName) || !is_array($actionDefinition)) {
                continue;
            }

            $permissionSpec = $this->permissionSpecParser->extract($actionDefinition);
            if ($permissionSpec === null) {
                continue;
            }

            if ($this->authorizer->isDiscoveryAllowed($permissionSpec)) {
                $filteredActions[$actionName] = $actionDefinition;
            }
        }

        return $filteredActions;
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

    /**
     * @param array $value
     * @return bool
     */
    private function isSequentialArray(array $value): bool
    {
        return !empty($value) && array_keys($value) === range(0, count($value) - 1);
    }
}
