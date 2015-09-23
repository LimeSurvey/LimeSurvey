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
     * @return boolean False if users from this authenticator can not be updated.
     */
    public function writable();
    
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