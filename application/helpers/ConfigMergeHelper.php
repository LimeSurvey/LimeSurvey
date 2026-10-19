<?php

namespace LimeSurvey\Helpers;

/**
 * Merges the user configuration (config.php) into the internal configuration (internal.php).
 */
class ConfigMergeHelper
{
    /**
     * Params holding integer-keyed option lists that must be replaced as a whole by the user configuration.
     * @var string[]
     */
    const REPLACED_PARAMS = ['pageSizeOptions', 'pageSizeOptionsTokens'];

    /**
     * Merges the user configuration into the internal configuration.
     *
     * CMap::mergeArray() appends integer-keyed entries that already exist under a new key instead of
     * replacing them, so option lists set in config.php are replaced instead of merged.
     *
     * @param array $internalConfig The configuration from internal.php
     * @param array $userConfig The configuration from config.php
     * @return array The merged configuration
     */
    public static function merge(array $internalConfig, array $userConfig): array
    {
        $result = \CMap::mergeArray($internalConfig, $userConfig);
        foreach (self::REPLACED_PARAMS as $param) {
            if (isset($userConfig['params'][$param]) && is_array($userConfig['params'][$param])) {
                $result['params'][$param] = $userConfig['params'][$param];
            }
        }
        return $result;
    }
}
