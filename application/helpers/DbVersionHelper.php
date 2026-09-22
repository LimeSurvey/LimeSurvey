<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

namespace LimeSurvey\Helpers;

/**
 * Helper to verify that the connected database server meets the documented
 * minimum version requirements for LimeSurvey.
 *
 * @link https://www.limesurvey.org/manual/Installation_-_LimeSurvey_CE
 */
class DbVersionHelper
{
    /** @var string Minimum supported MariaDB version */
    public const MINIMUM_MARIADB_VERSION = '10.3.38';

    /** @var string Minimum supported MySQL version */
    public const MINIMUM_MYSQL_VERSION = '8.0.0';

    /** @var string Minimum supported PostgreSQL version */
    public const MINIMUM_PGSQL_VERSION = '14';

    /** @var string Minimum supported Microsoft SQL Server version (comparison value) */
    public const MINIMUM_MSSQL_VERSION = '15.0';
    /** @var string Minimum supported Microsoft SQL Server version (shown to the user) */
    public const MINIMUM_MSSQL_LABEL = '2019';

    /**
     * Inspects the given driver name and server version string and returns
     * the minimum requirement details for that database type.
     *
     * @param string $driverName the PDO driver name (e.g. 'mysql', 'pgsql', 'sqlsrv')
     * @param string $serverVersion the raw server version string
     * @return array{type:string, minimum:string, minimumLabel:string, current:string, supported:bool}
     */
    public static function getRequirement($driverName, $serverVersion)
    {
        $driverName = strtolower((string)$driverName);
        $isMariaDb = stripos((string)$serverVersion, 'mariadb') !== false;
        // MariaDB 10+ prepends a "5.5.5-" replication compatibility prefix to its
        // reported version. Strip it so the real version can be extracted.
        $cleanVersion = preg_replace('/^5\.5\.5-/', '', (string)$serverVersion);
        // Accept both multi-segment ("10.3.38") and single-segment ("14") versions.
        $currentVersion = preg_match('/\d+(\.\d+)*/', $cleanVersion, $matches) ? $matches[0] : '';

        if (in_array($driverName, ['mysql', 'mysqli'])) {
            if ($isMariaDb) {
                return self::buildRequirement('MariaDB', self::MINIMUM_MARIADB_VERSION, $currentVersion);
            }
            return self::buildRequirement('MySQL', self::MINIMUM_MYSQL_VERSION, $currentVersion);
        }
        if ($driverName === 'pgsql') {
            return self::buildRequirement('PostgreSQL', self::MINIMUM_PGSQL_VERSION, $currentVersion);
        }
        if (in_array($driverName, ['sqlsrv', 'dblib', 'mssql'])) {
            return self::buildRequirement('Microsoft SQL Server', self::MINIMUM_MSSQL_VERSION, $currentVersion, self::MINIMUM_MSSQL_LABEL);
        }

        // Unknown driver: do not block, treat as supported.
        return [
            'type' => $driverName,
            'minimum' => '',
            'minimumLabel' => '',
            'current' => $currentVersion,
            'supported' => true,
        ];
    }

    /**
     * Convenience wrapper returning only whether the version is supported.
     *
     * @param string $driverName
     * @param string $serverVersion
     * @return bool
     */
    public static function isSupported($driverName, $serverVersion)
    {
        $requirement = self::getRequirement($driverName, $serverVersion);
        return $requirement['supported'];
    }

    /**
     * @param string $type human readable database type
     * @param string $minimum the version used for comparison
     * @param string $currentVersion the detected server version
     * @param string|null $minimumLabel the version shown to the user, defaults to $minimum
     * @return array{type:string, minimum:string, minimumLabel:string, current:string, supported:bool}
     */
    private static function buildRequirement($type, $minimum, $currentVersion, $minimumLabel = null)
    {
        return [
            'type' => $type,
            'minimum' => $minimum,
            'minimumLabel' => $minimumLabel ?? $minimum,
            'current' => $currentVersion,
            // If the version can't be detected, don't block the connection.
            'supported' => $currentVersion === '' || version_compare($currentVersion, $minimum, '>='),
        ];
    }
}
