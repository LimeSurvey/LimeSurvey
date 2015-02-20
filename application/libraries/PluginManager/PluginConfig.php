<?php
namespace ls\pluginmanager;
/**
 * Validates plugin configuration and updates the main configuration file.
 */
class PluginConfig extends \CFormModel
{
    /**
     *
     * @var PluginManager
     */
    public static $pluginManager;
    public $name;
    public $description;
    public $type;
    /**
     * @var string Vendor name
     */
    public $vendor;
 
    public $class;
    public $path;
    public $autoload = [];
    
    private $_events = [];
    
    public $apiVersion;
    
    public function setEvents($value) {
        $this->_events = array_unique($value);
    }
    public function getEvents() {
        return $this->_events;
    }
    public function __construct($config) {
        parent::__construct('');
        if (is_string($config)) {
            $this->scenario = 'register';
            $this->loadJsonFile($config);
        } elseif (is_array($config)) {
            $this->scenario = 'load';
            $this->setAttributes($config);
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
        $this->setAttributes($config);
    }
    
    public function rules() {
        return [
            [['name', 'description', 'vendor', 'class'], 'required'],
            ['type', 'in', 'range' => ['simple', 'module']],
            ['class', 'match', 'pattern' => '/^[[:alnum:]]+(\\\[[:alnum:]]+)*$/', 'allowEmpty' => false],
            ['autoload', 'validateAutoload'],
            ['apiVersion', 'in', 'range' => array_keys(self::$pluginManager->apiMap), 'on' => 'register', 'allowEmpty' => false],
            ['events', 'safe'],
            // Only trust these if they were loaded from our plugins.php
            [['path', 'apiVersion'], 'safe', 'on' => 'load'],
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
     * 
     * @return self[]
     */
    public static function loadMultiple(array $configurations) {
        $result = [];
        foreach ($configurations as $config) {
            $instance = new self($config);
            $result[$instance->id] = $instance;
        }
        return $result;
    }
    
    public static function scan($directory) {
        $result = [];
        if (is_dir($directory)) {
            $iterator = new \RecursiveDirectoryIterator($directory, 
                \FilesystemIterator::KEY_AS_FILENAME + 
                \FilesystemIterator::SKIP_DOTS +
                \FilesystemIterator::UNIX_PATHS
            );
            foreach(new \RecursiveIteratorIterator($iterator) as $fileName => $fileInfo) {
                if (strcmp($fileName, 'limesurvey.json') == 0) {
                    $result[] = $fileInfo->getPathName();
                }
            }
        }
        return $result;
    }
    
    /**
     * Scans the directory for plugins.
     * @param string $directory
     * @return array For each plugin an array with keys: success, name, errors.
     */
    public static function readAll($directory = []) {
        \Yii::beginProfile('readAll');
        $files = self::scan($directory);
        $configs = [];
        foreach ($files as $file) {
            $config = new self($file);
            $configs[] = $config;
        }
        \Yii::endProfile('readAll');
        return $configs;
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
    
    /**
     * 
     * @return iPlugin
     */
    public function getPlugin() {
        return self::$pluginManager->getPlugin($this->id);
    }
    
    public function getActive() {
        return self::$pluginManager->isActive($this->id);
    }
}