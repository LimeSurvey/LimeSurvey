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
     * @return array Returns the (admin) updateable settings.
     */
    public function getSettings();
    
    /**
     * @return array Returns the (self) updateable settings.
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
     * @return iAuthenticationPlugin Returns the authenticator this user belongs to.
     */
    public function getAuthenticator();
}