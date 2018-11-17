<?php

namespace LimeSurvey\PluginManager;

/**
 * Base class for plugins.
 */
abstract class PluginBase implements iPlugin
{
    /**
     *
     * @var LimesurveyApi
     */
    protected $api = null;

    /**
     *
     * @var PluginEvent
     */
    protected $event = null;

    protected $id = null;
    protected $storage = 'DummyStorage';

    static protected $description = 'Base plugin object';
    static protected $name = 'PluginBase';
    private $store = null;
    protected $settings = array();

    /**
     * This holds the pluginmanager that instantiated the plugin
     *
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * If plugin has a config.json file, it will be parsed into this variable.
     * @var StdObject
     */
    protected $config = null;

    /**
     * Constructor for the plugin
     * @todo Add proper type hint in 3.0
     * @param PluginManager $manager    The plugin manager instantiating the object
     * @param int           $id         The id for storage
     */
    public function __construct(PluginManager $manager, $id)
    {
        $this->pluginManager = $manager;
        $this->id = $id;
        $this->api = $manager->getAPI();

        $this->setLocaleComponent();
    }

    /**
     * We need a component for each plugin to load correct
     * locale file.
     *
     * @return void
     */
    protected function setLocaleComponent()
    {
        $basePath = $this->getDir().DIRECTORY_SEPARATOR.'locale';

        // No need to load a component if there is no locale files
        if (!file_exists($basePath)) {
            return;
        }

        // Set plugin specific locale file to locale/<lang>/<lang>.mo
        \Yii::app()->setComponent('pluginMessages'.$this->id, array(
            'class' => 'LSCGettextMessageSource',
            'cachingDuration' => 3600,
            'forceTranslation' => true,
            'useMoFile' => true,
            'basePath' => $basePath
        ));
    }

    /**
     * This function retrieves plugin data. Do not cache this data; the plugin storage
     * engine will handling caching. After the first call to this function, subsequent
     * calls will only consist of a few function calls and array lookups.
     *
     * @param string $key
     * @param string $model
     * @param int $id
     * @param mixed $default The default value to use when not was set
     * @return boolean
     */
    protected function get($key = null, $model = null, $id = null, $default = null)
    {
        return $this->getStore()->get($this, $key, $model, $id, $default);
    }

    /**
     * Return the description for this plugin
     */
    public static function getDescription()
    {
        return static::$description;
    }

    /**
     * Get the current event this plugin is responding to
     *
     * @return PluginEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Returns the id of the plugin
     *
     * Used by storage model to find settings specific to this plugin
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     *
     */
    public function getPluginSettings($getValues = true)
    {

        $settings = $this->settings;
        foreach ($settings as $name => &$setting) {
            if ($getValues) {
                $setting['current'] = $this->get($name, null, null, isset($setting['default']) ? $setting['default'] : null);
            }
            if ($setting['type'] == 'logo') {
                $setting['path'] = $this->publish($setting['path']);
            }
        }
        return $settings;
    }

    public static function getName()
    {
        return static::$name;
    }
    /**
     * Returns the plugin storage and takes care of
     * instantiating it
     *
     * @return iPluginStorage
     */
    public function getStore()
    {
        if (is_null($this->store)) {
            $this->store = $this->pluginManager->getStore($this->storage);
        }

        return $this->store;
    }

    /**
     * Publishes plugin assets.
     * @return string
     */
    public function publish($fileName)
    {
        // Check if filename is relative.
        if (strpos('//', $fileName) === false) {
            // This is a limesurvey relative path.
            if (strpos('/', $fileName) === 0) {
                $url = \Yii::getPathOfAlias('webroot').$fileName;

            } else {
// This is a plugin relative path. 
                $path = \Yii::getPathOfAlias('webroot.plugins.'.get_class($this)).DIRECTORY_SEPARATOR.$fileName;
                /*
                 * By using the asset manager the assets are moved to a publicly accessible path.
                 * This approach allows a locked down plugin directory that is not publicly accessible.
                 */
                $url = App()->assetManager->publish($path);
            }
        } else {
            $url = $fileName;
        }
        return $url;
    }

    /**
     *
     * @param string $name Name of the setting.
     * The type of the setting is either a basic type or choice.
     * The choice type is either a single or a multiple choice setting.
     * @param array $options
     * Contains parameters for the setting. The 'type' key contains the parameter type.
     * The type is one of: string, int, float, choice.
     * Supported keys per type:
     * String: max-length(int), min-length(int), regex(string).
     * Int: max(int), min(int).
     * Float: max(float), min(float).
     * Choice: choices(array containing values as keys and names as values), multiple(bool)
     * Note that the values for choice will be translated.
     */
    protected function registerSetting($name, $options = array('type' => 'string'))
    {
        $this->settings[$name] = $options;
    }

    /**
     *
     * @param type $settings
     */
    public function saveSettings($settings)
    {
        foreach ($settings as $name => $setting) {
            $this->set($name, $setting);
        }
    }


    /**
     * This function stores plugin data.
     *
     * @param string $key
     * @param mixed $data
     * @param string $model
     * @param int $id
     * @return boolean
     */
    protected function set($key, $data, $model = null, $id = null)
    {
        return $this->getStore()->set($this, $key, $data, $model, $id);
    }

    /**
     * Set the event to the plugin, this method is executed by the PluginManager
     * just before dispatching the event.
     *
     * @param PluginEvent $event
     * @return PluginBase
     */
    public function setEvent(PluginEvent $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Here you should handle subscribing to the events your plugin will handle
     */
    //abstract public function registerEvents();

    /**
     * This function subscribes the plugin to receive an event.
     *
     * @param string $event
     * @param string $function
     */
    protected function subscribe($event, $function = null)
    {
        return $this->pluginManager->subscribe($this, $event, $function);
    }

    /**
     * This function unsubscribes the plugin from an event.
     * @param string $event
     */
    protected function unsubscribe($event)
    {
        return $this->pluginManager->unsubscribe($this, $event);
    }

    /**
     * To find the plugin locale file, we need late runtime result of __DIR__.
     * Solution copied from http://stackoverflow.com/questions/18100689/php-dir-evaluated-runtime-late-binding
     *
     * @return string
     */
    protected function getDir()
    {
        $reflObj = new \ReflectionObject($this);
        $fileName = $reflObj->getFileName();
        return dirname($fileName);
    }

    /**
     * Look for views in plugin views/ folder and render it
     *
     * @param string $viewfile Filename of view in views/ folder
     * @param array $data
     * @param boolean $return
     * @param boolean $processOutput
     * @return string;
     */
    public function renderPartial($viewfile, $data, $return = false, $processOutput = false)
    {
        $alias = 'plugin_views_folder'.$this->id;
        \Yii::setPathOfAlias($alias, $this->getDir());
        $fullAlias = $alias.'.views.'.$viewfile;

        if (isset($data['plugin'])) {
            throw new InvalidArgumentException("Key 'plugin' in data variable is for plugin base only. Please use another key name.");
        }

        // Provide this so we can use $plugin->gT() in plugin views
        $data['plugin'] = $this;

        return \Yii::app()->controller->renderPartial($fullAlias, $data, $return, $processOutput);
    }

    /**
     * Translation for plugin
     *
     * @param string $sToTranslate The message that are being translated
     * @param string $sEscapeMode
     * @param string $sLanguage
     * @return string
     */
    public function gT($sToTranslate, $sEscapeMode = 'html', $sLanguage = null)
    {
        $translation = \quoteText(
            \Yii::t(
                '',
                $sToTranslate,
                array(),
                'pluginMessages'.$this->id,
                $sLanguage
            ),
            $sEscapeMode
        );

        // If we don't have a translation from the plugin, check core translations
        if ($translation == $sToTranslate) {
            $translationFromCore = \quoteText(
                \Yii::t(
                    '',
                    $sToTranslate,
                    array(),
                    null,
                    $sLanguage
                ),
                $sEscapeMode
            );

            return $translationFromCore;
        }

        return $translation;

    }

    /**
     * Call the Yii::log function to log into tmp/runtime/plugin.log
     * The plugin name is the category.
     *
     * @param string $message
     * @param string $level From CLogger, defaults to CLogger::LEVEL_TRACE
     * @return void
     */
    public function log($message, $level = \CLogger::LEVEL_TRACE)
    {
        $category = $this->getName();
        \Yii::log($message, $level, 'plugin.'.$category);
    }

    /**
     * Read JSON config file and store it in $this->config
     * Assumes config file is config.json and in plugin root folder.
     * @return void
     */
    public function readConfigFile()
    {
        $file = $this->getDir().DIRECTORY_SEPARATOR.'config.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $this->config = json_decode($json);

            if ($this->config === null) {
                // Failed. Popup error message.
                $this->showConfigErrorNotification();
            } else if ($this->configIsNewVersion()) {
                // Do everything related to reading config fields
                // TODO: Create a config object for this? One object for each config field? Then loop through those fields.
                $pluginModel = \Plugin::model()->findByPk($this->id);

                // "Impossible"
                if (empty($pluginModel)) {
                    throw new \Exception('Internal error: Found no database entry for plugin id '.$this->id);
                }

                $this->checkActive($pluginModel);
                $this->saveNewVersion($pluginModel);
            }
        } else {
            /* No need action since system update in 4.0, where need a config.xml */
        }
    }

    /**
     * Check if config field active is 1. If yes, activate the plugin.
     * This is the 'active-by-default' feature.
     * @param \Plugin $pluginModel
     * @return void
     */
    protected function checkActive($pluginModel)
    {
        if ($this->config->active == 1) {
            // Activate plugin
            $result = App()->getPluginManager()->dispatchEvent(
                new PluginEvent('beforeActivate', App()->getController()),
                $this->getName()
            );

            if ($result->get('success') !== false) {
                $pluginModel->active = 1;
                $pluginModel->update();
            } else {
                // Failed. Popup error message.
                $not = new \Notification(array(
                    'user_id' => App()->user->id,
                    'title' => gT('Plugin error'),
                    'message' =>
                        '<span class="fa fa-exclamation-circle text-warning"></span>&nbsp;'.
                        gT('Could not activate plugin '.$this->getName()).'. '.
                        gT('Reason:').' '.$result->get('message'),
                    'importance' => \Notification::HIGH_IMPORTANCE
                ));
                $not->save();
            }
        }
    }

    /**
     * Show an error message about malformed config.json file.
     * @return void
     */
    protected function showConfigErrorNotification()
    {
        $not = new \Notification(array(
            'user_id' => App()->user->id,
            'title' => gT('Plugin error'),
            'message' =>
                '<span class="fa fa-exclamation-circle text-warning"></span>&nbsp;'.
                gT('Could not read config file for plugin '.$this->getName()).'. '.
                gT('Config file is malformed or null.'),
            'importance' => \Notification::HIGH_IMPORTANCE
        ));
        $not->save();
    }

    /**
     * Returns true if config file has a higher version than database.
     * Assumes $this->config is set.
     * @return boolean
     */
    protected function configIsNewVersion()
    {
        if (empty($this->config)) {
            throw new \InvalidArgumentException('config is not set');
        }

        $pluginModel = \Plugin::model()->findByPk($this->id);

        return empty($pluginModel->version) ||
            version_compare($pluginModel->version, $this->config->version) === -1;
    }

    /**
     * Saves the new version from config into database
     * @return void
     */
    protected function saveNewVersion()
    {
        $pluginModel = \Plugin::model()->findByPk($this->id);
        $pluginModel->version = $this->config->version;
        $pluginModel->update();
    }


    protected function registerScript($relativePathToScript, $parentPlugin=null){
        
        $parentPlugin = $parentPlugin===null ? get_class($this) : $parentPlugin;

        $scriptToRegister = null;
        if(file_exists(YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToScript)) {
            $scriptToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToScript
            );
        } else if (file_exists(Yii::app()->getBasePath().'/plugins/'.$parentPlugin.'/'.$relativePathToScript)) {
            $scriptToRegister = Yii::app()->getAssetManager()->publish(
                Yii::app()->getBasePath().'/plugins/'.$parentPlugin.'/'.$relativePathToScript
            );
        } else if (file_exists(Yii::app()->getBasePath().'/application/core/plugins/'.$parentPlugin.'/'.$relativePathToScript)) {
            $scriptToRegister = Yii::app()->getAssetManager()->publish(
                Yii::app()->getBasePath().'/application/core/plugins/'.$parentPlugin.'/'.$relativePathToScript
            );
        }
        Yii::app()->getClientScript()->registerScriptFile($scriptToRegister);
    }

    protected function registerCss($relativePathToCss, $parentPlugin=null){
        
        $parentPlugin = $parentPlugin===null ? get_class($this) : $parentPlugin;

        $cssToRegister = null;
        if(file_exists(YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToCss)) {
            $cssToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToCss
            );
        } else if (file_exists(YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToCss)) {
            $cssToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToCss
            );
        } else if (file_exists(Yii::app()->getBasePath().'/application/core/plugins/'.$parentPlugin.'/'.$relativePathToCss)) {
            $cssToRegister = Yii::app()->getAssetManager()->publish(
                Yii::app()->getBasePath().'/application/core/plugins/'.$parentPlugin.'/'.$relativePathToCss
            );
        }
        Yii::app()->getClientScript()->registerCssFile($cssToRegister);
    }

}
