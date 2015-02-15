<?php
namespace ls\pluginmanager;

abstract class PluginModule extends \CWebModule implements iPlugin
{
    use PluginTrait;
    /**
     *
     * @var PluginConfig
     */
    protected $pluginConfig;
    
    public function getDescription() {
        return $this->pluginConfig->description;
    }
}
