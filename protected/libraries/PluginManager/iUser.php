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
     * @return array Returns the settings that can be updated by an admin.
     */
    public function getSettings();
    
    /**
     * @return array Returns the settings that can be updated by the user
     */
    public function getProfileSettings();
    
    /**
     * 
     */
    public function setSettings($settings);
    
    /**
     * 
     */
    public function setProfileSettings($settings);
    
    /**
     * @return AuthenticationPluginInterface Returns the authenticator this user belongs to.
     */
    public function getAuthenticator();
}