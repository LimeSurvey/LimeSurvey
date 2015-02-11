<?php
namespace ls\pluginmanager;

interface iAuthenticationPlugin {
    
    /**
     * 
     * @param \CHttpRequest $request
     * @return iUser;
     */
    public function authenticate(\CHttpRequest $request);
    public function getLoginSettings();
    
    /**
     * @return boolean Return true if this authentication plugin can enumerate all users.
     */
    public function enumerable();
    
    /**
     * @return \IDataProvider
     */
    public function getUsers();
    
    /**
     * 
     * @param mixed $id;
     * @return iUser;
     */
    public function getUser($id);
}