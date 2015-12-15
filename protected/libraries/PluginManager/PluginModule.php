<?php
namespace ls\pluginmanager;

abstract class PluginModule extends \CWebModule implements PluginInterface
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
