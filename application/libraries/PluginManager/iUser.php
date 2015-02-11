<?php
namespace ls\pluginmanager;

interface iUser {
    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getId();
    
    /**
     * @return string
     */
    public function getLanguage();
    
    /**
     * @return array Returns the updateable settings.
     */
    public function getSettings();
    /**
     * 
     */
    public function setSettings($settings);
}