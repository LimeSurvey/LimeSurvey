<?php
namespace ls\pluginmanager;
class PluginConfig extends \CFormModel
{
    protected static $pluginConfig;
    protected static $plugins;
    public static $pluginManager;
    public $name;
    public $description;
    public $type;
    /**
     * @var string Vendor name
     */
    public $vendor;
 
    public $class;
    /**
     * @var boolean
     */
    public $active = false;
    
    public $path;
    public $autoload = [];
    
    public $events = [];
    
    public $apiVersion;
    
    public function __construct($config) {
        parent::__construct('');
        self::loadPluginConfig();
        if (is_string($config)) {
            $this->scenario = 'register';
            $this->loadJsonFile($config);
        } elseif (is_array($config)) {
            $this->scenario = 'load';
            $this->setAttributes($config);
        }
    }
    
    protected static function loadPluginConfig() {
        if (!isset(self::$pluginConfig)) {
            $file = \Yii::getPathOfAlias('application.config') . '/plugins.php';
            self::$pluginConfig = file_exists($file) ? include($file) : [];
        }
    }
    public function loadJsonFile($configFile) {
        if (!file_exists($configFile)) {
            throw new \Exception("Config file not found.");
        }
        
        $json = file_get_contents($configFile);
        $config = json_decode($json, true);
        if ($config === false) {
            throw new \Exception("Config file not does not contain valid JSON.");
        }
        $this->path = dirname(realpath($configFile));
        if (isset(self::$pluginConfig[$config['name']])) {
            $this->setAttributes(self::$pluginConfig[$config['name']], false);
        }
        $this->setAttributes($config);
    }
    
    public function rules() {
        return [
            [['name', 'description', 'vendor'], 'required'],
            ['type', 'in', 'range' => ['simple', 'module']],
            ['class', 'match', 'pattern' => '/^[[:alnum:]]+(\\\[[:alnum:]]+)*$/'],
            ['autoload', 'validateAutoload'],
            ['apiVersion', 'in', 'range' => array_keys(self::$pluginManager->apiMap), 'on' => 'register', 'allowEmpty' => false],
            ['events', 'safe'],
            // Only trust these if they were loaded from our plugins.php
            [['path', 'active', 'apiVersion'], 'safe', 'on' => 'load'],
        ];
    }
    
    public function validateAutoload($attribute) {
        $config = $this->$attribute;
        $supported = ['psr-4'];
        if (count(array_diff(array_keys($config), $supported)) > 0) {
            $this->addError($attribute, "At this moment the supported keys are: " . implode(', ', $supported));
        }
        
        if (isset($config['psr-4'])) {
            // Validate PSR-4 config.
            foreach($config['psr-4'] as $prefix => $paths) {
                $paths = is_string($paths) ? [$paths] : $paths;
                foreach ($paths as $path) {
                    if (!is_dir("{$this->path}/$path")) {
                        $this->addError($attribute, "Path $path for $prefix is invalid.");
                    }
                }
            }
        }
//        var_dump($this->path);
    }
    
    /**
     * Saves the plugin information to config/plugins.php
     * @param boolean $runValidation
     * @param boolean $write If false only updates static configuration array.
     */
    public function save($runValidation = true, $write = true) {
        if ($this->validate()) {
            $file = new \PhpConfigFile(\Yii::getPathOfAlias('application.config') . '/plugins.php');
            self::$pluginConfig[$this->name] = $this->attributes;
            $file->setConfig(self::$pluginConfig, false);
            return $file->save();
        }
        return false;
        
    }
            
    /**
     * 
     * @return self[]
     */
    public static function findAll($activeOnly = true) {
        if (!isset(self::$plugins)) {
            self::loadPluginConfig();
            $plugins = [];
            foreach(self::$pluginConfig as $config) {
                $instance = new self($config);
                $plugins[$instance->id] = $instance;
            }
            self::$plugins = $plugins;
        }
        return !$activeOnly ? self::$plugins : array_filter(self::$plugins, function(PluginConfig $config) {
            return $config->active;
        });

    }
    public static function scan($directory) {
        $iterator = new \RecursiveDirectoryIterator($directory, 
            \FilesystemIterator::CURRENT_AS_PATHNAME +
            \FilesystemIterator::KEY_AS_FILENAME + 
            \FilesystemIterator::SKIP_DOTS +
            \FilesystemIterator::UNIX_PATHS
        );
        foreach(new \RecursiveIteratorIterator($iterator) as $fileName => $filePath) {
            if (strcmp($fileName, 'limesurvey.json') == 0) {
                $result[] = $filePath;
            }
        }
        return $result;
    }
    
    /**
     * Scans the directory for plugins and registers them all in the plugins.php file.
     * @param string $directory
     * @return array For each plugin an array with keys: success, name, errors.
     */
    public static function registerAll($directory) {
        $files = self::scan($directory);
        return array_map([__CLASS__, 'register'], $files);
    }
    
    public static function register($configFile) {
        $config = new PluginConfig($configFile);
        $config->save();
        return $config;
    }
    
    public function registerNamespace(\Composer\Autoload\ClassLoader $loader, $runValidation = true) {
        if ((!$runValidation || $this->validate()) && isset($this->autoload['psr-4'])) {
            foreach ($this->autoload['psr-4'] as $prefix => $paths) {
                $paths = is_string($paths) ? [$paths] : $paths;
                foreach ($paths as $path) {
                    $loader->addPsr4($prefix, "{$this->path}/$path");
                }
            }
        }
    }
    
    public function attributeNames() {
        $names = parent::attributeNames();
//        $names[] = 'qualifiedClassName';
        return $names;
    }
    
    
    public function createPlugin(PluginManager $manager) {
        $class = $this->class;
        $instance = new $class($manager);
        $instance->name = $this->name;
        $instance->id = $this->id;
        foreach($this->events as $event) {
            $function = "event" . ucfirst($event);
            $callable = [$instance, $function];
            if (!is_callable($callable, false, $name)) {
                throw new \Exception("Could not register event $event for plugin {$this->name}, does $name exist?");
            }
            $manager->subscribe($event, $callable);
        }
        return $instance;
    }
    
    public function getId() {
        return strtr($this->class, ['\\' => '_']);
    }
}