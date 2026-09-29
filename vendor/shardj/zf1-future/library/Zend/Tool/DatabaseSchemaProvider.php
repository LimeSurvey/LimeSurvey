<?php
class Zend_Tool_DatabaseSchemaProvider extends Zend_Tool_Project_Provider_Abstract
{
    /**
     * @var Zend_Db_Adapter_Interface
     */
    protected $_db;

    /**
     * @var string
     */
    protected $_tablePrefix;

    /**
     * @var Zend_Config
     */
    protected $_config;

    /**
     * Section name to load from config
     * @var string
     */
    protected $_appConfigSectionName;

    public function update($env='development', $dir='./scripts/migrations')
    {
        return $this->updateTo(null, $env, $dir);
    }

    /**
     * Allows you to change the database schema version by specifying the desired version. If you are
     * upgrading (choosing a higher version), it will update to the highest version that is available.
     * If you are downgrading, it will go to the highest version that is equal to or lower than the
     * version you specified.
     *
     * @param string $version Version to change to
     * @param string $env     Environment to retrieve database credentials from, default is development
     * @param string $dir     Directory containing migration files, default is ./scripts/migrations
     *
     * @return boolean
     */
    public function updateTo($version, $env='development', $dir='./scripts/migrations')
    {
        $this->_init($env);
        $response = $this->_registry->getResponse();
        try {
            $db = $this->_getDbAdapter();
            $manager = new Zend_Db_Schema_Manager($dir, $db, $this->getTablePrefix());

            $result = $manager->updateTo($version);

            switch ($result) {
                case Zend_Db_Schema_Manager::RESULT_AT_CURRENT_VERSION:
                    if (!$version) {
                        $version = $manager->getCurrentSchemaVersion();
                    }
                    $response->appendContent("Already at version $version");
                    break;

                case Zend_Db_Schema_Manager::RESULT_NO_MIGRATIONS_FOUND :
                    $response->appendContent("No migration files found to migrate from {$manager->getCurrentSchemaVersion()} to $version");
                    break;

                default:
                    $response->appendContent('Schema updated to version ' . $manager->getCurrentSchemaVersion());
            }

            return true;
        } catch (Exception $e) {
            $response->appendContent('AN ERROR HAS OCCURED:');
            $response->appendContent($e->getMessage());
            $response->appendContent($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Decrements the database schema version to the next version or if specified
     * down a specified number of versions.
     *
     * @param int    $versions Number of versions to decrement. Default is 1
     * @param string $env      Environment to read database credentials from
     * @param string $dir      Directory containing migration files
     *
     * @return boolean
     */
    public function decrement($versions=1, $env='development', $dir='./scripts/migrations')
    {
    	$this->_init($env);
        $response = $this->_registry->getResponse();
        try {
            $db = $this->_getDbAdapter();
            $manager = new Zend_Db_Schema_Manager($dir, $db, $this->getTablePrefix());

            $result = $manager->decrementVersion($versions);

            switch ($result) {
                case Zend_Db_Schema_Manager::RESULT_AT_MINIMUM_VERSION:
                    $response->appendContent("Already at minimum version " . $manager->getCurrentSchemaVersion());
                    break;

                default:
                    $response->appendContent('Schema updated to version ' . $manager->getCurrentSchemaVersion());
            }

            return true;
        } catch (Exception $e) {
            $response->appendContent('AN ERROR HAS OCCURRED: ');
            $response->appendContent($e->getMessage());
            $response->appendContent($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Increments the datbase schema version to the next version or up a specified
     * number of versions
     *
     * @param int    $versions Number of versions to increment. Default is 1
     * @param string $env      Environment to read database conguration from
     * @param string $dir      Directory containing migration scripts
     *
     * @return bool
     */
    public function increment($versions=1,$env='development', $dir='./scripts/migrations')
    {
    	$this->_init($env);
        $response = $this->_registry->getResponse();
        try {
            $db = $this->_getDbAdapter();
            $manager = new Zend_Db_Schema_Manager($dir, $db, $this->getTablePrefix());

            $result = $manager->incrementVersion($versions);

            switch ($result) {
                case Zend_Db_Schema_Manager::RESULT_AT_MAXIMUM_VERSION:
                    $response->appendContent("Already at maximum version " . $manager->getCurrentSchemaVersion());
                    break;

                default:
                    $response->appendContent('Schema updated to version ' . $manager->getCurrentSchemaVersion());
            }

            return true;
        } catch (Exception $e) {
            $response->appendContent('AN ERROR HAS OCCURED:');
            $response->appendContent($e->getMessage());
            $response->appendContent($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Provide the current schema version number
     *
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function current($env='development', $dir='./migrations')
    {
        $this->_init($env);
        try {

            // Initialize and retrieve DB resource
            $db = $this->_getDbAdapter();
            $manager = new Zend_Db_Schema_Manager($dir, $db, $this->getTablePrefix());
            echo 'Current schema version is ' . $manager->getCurrentSchemaVersion() . PHP_EOL;

            return true;
        } catch (Exception $e) {
            echo 'AN ERROR HAS OCCURED:' . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
            return false;
        }
    }

    /**
     * Retrieves the realpath for ./scripts/migrations. Does not appear to be
     * used anywhere. Possible candidate for removal.
     *
     * @return string
     * @deprecated
     */
    protected function _getDirectory()
    {
        $dir = './scripts/migrations';
        return realpath($dir);
    }

    /**
     * Initializes the Akrabat functionality and adds it to Zend_Tool (zf)
     *
     * @param string $env Environment to initialize for
     *
     * @return null
     *
     * @throws Zend_Tool_Project_Exception
     */
    protected function _init($env)
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $appConfigFileResource = $profile->search('applicationConfigFile');

        if ($appConfigFileResource == false) {
            throw new Zend_Tool_Project_Exception('A project with an application config file is required to use this provider.');
        }
        $appConfigFilePath = $appConfigFileResource->getPath();

        // Base config, normally the application.ini in the configs dir of your app
        $this->_config = $this->_createConfig($appConfigFilePath, $env, true);

        // Are there any override config files?
        foreach($this->_getAppConfigOverridePathList($appConfigFilePath) as $path) {
            $overrideConfig = $this->_createConfig($path);
            if (isset($overrideConfig->$env)) {
                $this->_config->merge($overrideConfig->$env);
            }
        }

        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Zend_');
    }

    /**
     * Pull the akrabat section of the zf.ini
     *
     *  @return Zend_Config_Ini|false Fasle if not set
     */
    protected function _getUserConfig()
    {
        $userConfig = false;
        if (isset($this->_registry->getConfig()->akrabat)) {
            $userConfig = $this->_registry->getConfig()->akrabat;
        }
        return $userConfig;
    }

    /**
     * Create new Zend_Config object based on a filename
     *
     * Mostly a copy and paste from Zend_Application::_loadConfig
     *
     * @param string $filename           File to create the object from
     * @param string $section            If not null, pull this sestion of the config
     * file only. Doesn't apply to .php and .inc file
     * @param string $allowModifications Should the object be mutable or not
     *
     * @throws Zend_Db_Schema_Exception
     *
     * @return Zend_Config
     */
    protected function _createConfig($filename, $section = null, $allowModifications = false) {

        $options = false;
        if ($allowModifications) {
            $options = ['allowModifications' => true];
        }

        $suffix = pathinfo($filename, PATHINFO_EXTENSION);
        $suffix  = ($suffix === 'dist')
                 ? pathinfo(basename($filename, ".$suffix"), PATHINFO_EXTENSION)
                 : $suffix;

        switch (strtolower($suffix)) {
            case 'ini':
                $config = new Zend_Config_Ini($filename, $section, $options);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($filename, $section, $options);
                break;

            case 'json':
                $config = new Zend_Config_Json($filename, $section, $options);
                break;

            case 'yaml':
            case 'yml':
                $config = new Zend_Config_Yaml($filename, $section, $options);
                break;

            case 'php':
            case 'inc':
                $config = include $filename;
                if (!is_array($config)) {
                    throw new Zend_Db_Schema_Exception(
                        'Invalid configuration file provided; PHP file does not return array value'
                    );
                }
                $config = new Zend_Config($config, $allowModifications);
                break;

            default:
                throw new Zend_Db_Schema_Exception(
                    'Invalid configuration file provided; unknown config type'
                );
        }
        return $config;
    }

    /**
     * Will pull a list of file paths to application config overrides
     *
     * There is a deliberate attempt to be very forgiving. If a file doesn't exist,
     * it won't be included in the list. If a the file doesn't have a section that
     * corresponds the current target environment it don't be merged.
     *
     * The config files should be standalone, they will not be able to extend
     * sections from the base config file.
     *
     * The ini, xml, json, yaml and php config file types are supported
     *
     * By default we will look for a "local.ini" in the applications configs
     * directory.
     *
     * Config files are added with an order, the order run from lowest to highest.
     * The "local.ini" file in this case will be given the order of 100
     *
     * This can be disabled with the following in your .zf.ini:
     *
     * akrabat.appConfigOverride.skipLocal = true
     *
     * You can have add to the list of file names to look for in the configs
     * directory by adding the following to the .zf.ini:
     *
     * akrabat.appConfigOverride.name = 'override.ini'
     *
     * You can only add one name with this approach and it will be added with the
     * order of 200
     *
     * To add mutiple names to be checked use the following in the .zf.ini:
     *
     * akrabat.appConfigOverride.name.60 = 'dev.ini'
     * akrabat.appConfigOverride.name.50 = 'override.ini.ini'
     *
     * Where the last part of the config key is the order to merge the files.
     *
     * To add a path to be include, do the following in your .zf.ini:
     *
     * akrabat.appConfigOverride.path = '/home/user/projects/account/configs/local.ini'
     *
     * You can only add one path with this approach and it will be added with the
     * order of 300
     *
     * To add mutiple path use the following in the .zf.ini:
     *
     * akrabat.appConfigOverride.path.1 = './application/configs/dev.ini'
     * akrabat.appConfigOverride.path.4 = '/home/user/projects/account/configs/local.ini'
     *
     * Where the last part of the config key is the order to merge the files.
     *
     * If a path is added with an order that clashes with another file then the
     * path will be added the end of the queue
     *
     * @param string $appConfigFilePath
     *
     * @return array
     */
    protected function _getAppConfigOverridePathList($appConfigFilePath)
    {
        $pathList     = [];
        $appConfigDir = dirname($appConfigFilePath);
        $userConfig   = false;

        if ($this->_getUserConfig() !== false
            && isset($this->_getUserConfig()->appConfigOverride)
        ) {
            $userConfig = $this->_getUserConfig()->appConfigOverride;
        }

        $skipLocal = false;
        if ($userConfig !== false && isset($userConfig->skipLocal)) {
            $skipLocal = (bool)$userConfig->skipLocal;
        }

        // The convention over configuration option
        if ($skipLocal === false) {
            $appConfigFilePathLocal = realpath($appConfigDir.'/local.ini');
            if ($appConfigFilePathLocal) {
                $pathList[100] = $appConfigFilePathLocal;
            }
        }

        if ($userConfig === false) {
            return $pathList;
        }

        // Look for file names in the app configs dir
        if (isset($userConfig->name)) {
            if ($userConfig->name instanceof Zend_Config) {
                $fileNameList = $userConfig->name->toArray();
            } else {
                $fileNameList = [200 => $userConfig->name];
            }

            foreach($fileNameList as $order => $fileName) {
                $path = realpath($appConfigDir.'/'.$fileName);
                if ($path) {
                    $pathList[$order] = $appConfigDir.'/'.$fileName;
                }
            }
        }

        // A full or relative path, app dir will not be prefixed
        if (isset($userConfig->path)) {
            if ($userConfig->path instanceof Zend_Config) {
                $filePathList = $userConfig->path->toArray();
            } else {
                $filePathList = [300 => $userConfig->path];
            }

            foreach($filePathList as $order => $filePath) {
                if (file_exists($filePath) === false) {
                    continue;
                }
                if (isset($pathList[$order])) {
                    $pathList[] = $filePath;
                } else {
                    $pathList[$order] = $filePath;
                }
            }
        }

        ksort($pathList);
        return $pathList;
    }

    /**
     * Retrieve initialized DB connection
     *
     * @return Zend_Db_Adapter_Interface
     */
    protected function _getDbAdapter()
    {
        if ((null === $this->_db)) {
            if($this->_config->resources->db){
                $dbConfig = $this->_config->resources->db;
                $this->_db = Zend_Db::factory($dbConfig->adapter, $dbConfig->params);
            } elseif($this->_config->resources->multidb){
                foreach ($this->_config->resources->multidb as $db) {
                    if($db->default){
                        $this->_db = Zend_Db::factory($db->adapter, $db);
                    }
                }
            }
            if($this->_db instanceof Zend_Db_Adapter_Interface) {
                throw new Zend_Db_Schema_Exception('Database was not initialized');
            }
        }
        return $this->_db;
    }

    /**
     * Retrieve table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        if ((null === $this->_tablePrefix)) {
            $prefix = '';
            if (isset($this->_config->resources->db->table_prefix)) {
                $prefix = $this->_config->resources->db->table_prefix . '_';
            }
            $this->_tablePrefix = $prefix;
        }
        return $this->_tablePrefix;
    }

}
