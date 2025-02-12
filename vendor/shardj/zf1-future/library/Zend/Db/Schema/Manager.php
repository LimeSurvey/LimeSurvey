<?php
class Zend_Db_Schema_Manager
{
    public const RESULT_OK                  = 'RESULT_OK';
    public const RESULT_AT_CURRENT_VERSION  = 'RESULT_AT_CURRENT_VERSION';
    public const RESULT_NO_MIGRATIONS_FOUND = 'RESULT_NO_MIGRATIONS_FOUND';
    public const RESULT_AT_MAXIMUM_VERSION  = 'RESULT_AT_MAXIMUM_VERSION';
    public const RESULT_AT_MINIMUM_VERSION  = 'RESULT_AT_MINIMUM_VERSION';

    /**
     * @var string
     */
    protected $_schemaVersionTableName = 'schema_version';

    /**
     * Directory containing migration files
     * @var string
     */
    protected $_dir;

    /**
     * Database adapter
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Table prefix string for use by change classes
     * @var string
     */
    protected $_tablePrefix;

    /**
     * Constructor
     *
     * Available $options keys:
     * 		'table_prefix' => prefix string to place before table names
     * 		'schema_version_table_name' => name of table to use for holding the schema version number
     *
     *
     * @param string                   $dir         Directory where migrations files are stored
     * @param Zend_Db_Adapter_Abstract $db          Database adapter
     * @param string                   $tablePrefix Table prefix to be used by change files
     */
    public function __construct($dir, Zend_Db_Adapter_Abstract $db, $tablePrefix='')
    {
        $this->_dir = realpath($dir);
        $this->_db = $db;
        $this->_tablePrefix = $tablePrefix;
    }

    /**
     * Retrieves the current database schema version from the database
     *
     * If the table does not exist, it will be created and the version will
     * be set to 0.
     *
     * @return string
     */
    public function getCurrentSchemaVersion()
    {
        // Ensure we have valid connection to the database
        if (!$this->_db->isConnected()) {
            $this->_db->getServerVersion();
        }
        $schemaVersionTableName = $this->getPrefixedSchemaVersionTableName();

        $sql = "SELECT version FROM " . $schemaVersionTableName;
        try {
            $version = $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            // exception means that the schema version table doesn't exist, so create it
            $createSql = "CREATE TABLE $schemaVersionTableName (
                version bigint NOT NULL,
                PRIMARY KEY (version)
            )";
            $this->_db->query($createSql);
            $insertSql = "INSERT INTO $schemaVersionTableName (version) VALUES (0)";
            $this->_db->query($insertSql);
            $version = $this->_db->fetchOne($sql);
        }

        return $version;
    }

    /**
     * Updates the database schema to a specified version. If upgrading (increasing
     * version number) the schema version will be the largest available version
     * that is less than or equal to the specified version. ie, if the highest version
     * is 050 and 7000 is specified for $version, the resulting version will be
     * 050. If downgrading (decreasing version number) the ending version will be
     * the highest version that is less than or equal to the specified version
     * number. i.e, if versions 10, 15 and 20 exist and the version is updated
     * to 19, the resulting version will be 15 since version 20 will be downgraded.
     *
     * The method automatcally determines the direction of the migration by comparing
     * the current version (from the database) and the desired version. If they
     * are the same, no migration will be performed and the version will remain
     * the same.
     *
     * @param string $version
     *
     * @return string
     */
    public function updateTo($version = null)
    {
        if (is_null($version)) {
            $version = PHP_INT_MAX;
        }
        $version = (int)$version;
        $currentVersion = $this->getCurrentSchemaVersion();
        if($currentVersion == $version) {
            return self::RESULT_AT_CURRENT_VERSION;
        }

        $migrations = $this->_getMigrationFiles($currentVersion, $version);
        if(empty($migrations)) {
            if ($version == PHP_INT_MAX) {
                return self::RESULT_AT_CURRENT_VERSION;
            }
            return self::RESULT_NO_MIGRATIONS_FOUND;
        }

        $direction = 'up';
        if ($currentVersion > $version) {
            $direction = 'down';
        }
        foreach ($migrations as $migration) {
            $this->_processFile($migration, $direction);
        }

        // figure out what the real version we're going to is if going down
        // TODO: make this more efficient by caching file information instead
        // of fetching it again.
        if ($direction == 'down') {
        	$files = $this->_getMigrationFiles($version, 0);
        	if (empty($files)) {
        		$realVersion = 0;
        	} else {
            	$versionFile = array_shift($files);
        		$realVersion = $versionFile['version'];
        	}
        	// update the database to the version we're actually at
        	$this->_updateSchemaVersion($realVersion);
        }

        return self::RESULT_OK;
    }

    /**
     * Increments the database version a specified number of upgrades. For instance,
     * if $versions is 1, it will update to the next highest version of the database.
     *
     * If $versions is provided and less than 1, it will assume 1 and update
     * a single version. If a number higher than the available upgradable versions
     * is specified, it will update to the highest version number.
     *
     * If the database is already at the highest version number available, it will
     * not do anything and indicate it is at the maximum version number via
     * the return value.
     *
     * @param int $versions Number of versions to increment. Must be 1 or greater
     *
     * @return string
     */
    public function incrementVersion($versions)
    {
    	$versions = (int)$versions;
    	if ($versions < 1) {
    		$versions = 1;
    	}
    	$currentVersion = $this->getCurrentSchemaVersion();

    	$files = $this->_getMigrationFiles($currentVersion, PHP_INT_MAX);
    	if (empty($files)) {
    		return self::RESULT_AT_MAXIMUM_VERSION;
    	}

    	$files = array_slice($files, 0, $versions);

    	$nextFile = array_pop($files);
    	$nextVersion = $nextFile['version'];

    	return $this->updateTo($nextVersion);
    }

    /**
     * Decrements the version of the database by the specified number of versions.
     *
     * If the database is already at the lowest version number, it will indicate
     * this through the return value.
     *
     * @param int $versions Number of versions to decrement.
     *
     * @return string
     */
    public function decrementVersion($versions)
    {
    	$versions = (int)$versions;
    	if ($versions < 1) {
    		$versions = 1;
    	}
    	$currentVersion = $this->getCurrentSchemaVersion();

    	$files = $this->_getMigrationFiles($currentVersion, 0);
    	if (empty($files)) {
    		return self::RESULT_AT_MINIMUM_VERSION;
    	}

    	$files = array_slice($files, 0, $versions+1);
    	$nextFile = array_pop($files);
    	$nextVersion = $nextFile['version'];

    	return $this->updateTo($nextVersion);
    }

    /**
     * Retrieves the migration files that are needed to take the database from
     * its a specified version (current version) to the desired version. It
     * will also determine the direction of the migration and sort the files
     * accordingly.
     *
     * @param string $currentVersion Version to migrate database from
     * @param string $stopVersion    Version to migrate database to
     * @param string $dir            Directory containing migration files
     *
     * @throws Zend_Db_Schema_Exception
     *
     * @return array of file name, version and class name
     */
    protected function _getMigrationFiles($currentVersion, $stopVersion, $dir = null)
    {
        if ($dir === null) {
            $dir = $this->_dir;
        }

        $direction = 'up';
        $from = $currentVersion;
        $to  = $stopVersion;
        if($stopVersion < $currentVersion) {
            $direction = 'down';
            $from  = $stopVersion;
            $to = $currentVersion;
        }

        $files = [];
        if (!is_dir($dir) || !is_readable($dir)) {
        	return $files;
        }

        $d = dir($dir);
        $seen = [];
        while (false !== ($entry = $d->read())) {
            if (preg_match('/^([0-9]+)\-(.*)\.php/i', $entry, $matches) ) {
                $versionNumber = (int)$matches[1];
                if (isset($seen[$versionNumber])) {
                    throw new Zend_Db_Schema_Exception("version $versionNumber is used for multiple migrations.");
                }
                $seen[$versionNumber] = true;
                $className = $matches[2];
                if ($versionNumber > $from && $versionNumber <= $to) {
                    $path = $this->_relativePath($this->_dir, $dir);
                    $files["v{$matches[1]}"] = [
                        'path'=>$path,
                        'filename'=>$entry,
                        'version'=>$versionNumber,
                        'classname'=>$className];
                }
            } elseif ($entry != '.' && $entry != '..') {
                $subdir = $dir . '/' . $entry;
                if (is_dir($subdir) && is_readable($subdir)) {
                    $files = array_merge(
                        $files,
                        $this->_getMigrationFiles(
                            $currentVersion, $stopVersion, $subdir
                        )
                    );
                }
            }
        }
        $d->close();

        if($direction == 'up') {
            ksort($files);
        } else {
            krsort($files);
        }

        return $files;
    }

    /**
     * Runs a migration file according to the information provided. The
     * migration parameter is an array or object allowing ArrayAccess with the
     * following fields:
     *
     * version - The version of the migration this file represents
     * filename - The name of the file containing the code to upgrade or downgrade the database
     * classname - The name of the class contained in the file
     *
     * The direction parameter should be one of either "up" or "down" and indicates
     * which of the migration class methods should be executed. The up method is
     * assumed to move the database schema to the next version while down is
     * assumed to undo whatever up did.
     *
     * @param array|ArrayAccess $migration Information about the migration file
     * @param string            $direction "up" or "down"
     *
     * @throws Zend_Db_Schema_Exception
     *
     * @return null
     *
     * @todo I think there may be a problem with different migration files using
     * the same class name. -- Confirmed. If you migrate single versions at a time,
     * i.e. using increment or decrement then you will have no problems. If you
     * try to migrate through files where there are more than one file with a
     * particular class name, it will fail because it tries to redeclare a class
     * that already exists.
     */
    protected function _processFile($migration, $direction)
    {
        $path = $migration['path'];
        $version = $migration['version'];
        $filename = $migration['filename'];
        $classname = $migration['classname'];
        require_once($this->_dir.'/'.$path.'/'.$filename);
        if (!class_exists($classname, false)) {
            throw new Zend_Db_Schema_Exception("Could not find class '$classname' in file '$filename'");
        }
        $class = new $classname($this->_db, $this->_tablePrefix);
        $class->$direction();

        if($direction == 'down') {
            // current version is actually one lower than this version now
            $version--;
        }
        $this->_updateSchemaVersion($version);
    }

    /**
     * Updates the schema version in the database.
     *
     * @param int $version Version to update into database
     *
     * @return null
     */
    protected function _updateSchemaVersion($version)
    {
        $schemaVersionTableName = $this->getPrefixedSchemaVersionTableName();
        $sql = "UPDATE  $schemaVersionTableName SET version = " . (int)$version;
        $this->_db->query($sql);
    }

    /**
     * Retrieves the prefixed version of the schema version table.
     *
     * @return string
     */
    public function getPrefixedSchemaVersionTableName()
    {
        return $this->_tablePrefix . $this->_schemaVersionTableName;
    }

    /**
     * Returns a relative path from one directory to another
     *
     * @param string $from Directory to start from
     * @param string $to   Directory to end at
     * @param string $ps   Path seperator
     *
     * @return string
     */
    protected function _relativePath($from, $to, $ps = DIRECTORY_SEPARATOR)
    {
        $arFrom = explode($ps, rtrim($from, $ps));
        $arTo = explode($ps, rtrim($to, $ps));
        while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
            array_shift($arFrom);
            array_shift($arTo);
        }
        return str_pad("", count($arFrom) * 3, '..'.$ps).implode($ps, $arTo);
    }
}

