<?php

class AuthManager implements IAuthManager {
    /**
     * @var IAuthManager
     */
    public $authorizationPlugin;
    public function init() {
        $id = SettingGlobal::get('authorizationPlugin', 'ls_core_plugins_PermissionDb');
        $this->authorizationPlugin = App()->pluginManager->getPlugin($id);  
    }
    public function addItemChild($itemName, $childName) {
        return $this->authorizationPlugin->addItemChild($itemName, $childName);
    }

    public function assign($itemName, $userId, $bizRule = null, $data = null) {
        return $this->authorizationPlugin->assign($itemName, $userId, $bizRule, $data);
    }

    public function checkAccess($itemName, $userId, $params = array()) {
        return $this->authorizationPlugin->checkAccess($itemName, $userId, $params);
    }

    public function clearAll() {
        return $this->authorizationPlugin->clearAll();
    }

    public function clearAuthAssignments() {
        return $this->authorizationPlugin->clearAuthAssignments();
    }

    public function createAuthItem($name, $type, $description = '', $bizRule = null, $data = null) {
        return $this->authorizationPlugin->createAuthItem($name, $type, $description, $bizRule, $data);
    }

    public function executeBizRule($bizRule, $params, $data) {
        return $this->authorizationPlugin->executeBizRule($bizRule, $params, $data);
    }

    public function getAuthAssignment($itemName, $userId) {
        return $this->authorizationPlugin->getAuthAssignment($itemName, $userId);
    }

    public function getAuthAssignments($userId) {
        return $this->authorizationPlugin->getAuthAssignments($userId);
    }

    public function getAuthItem($name) {
        return $this->authorizationPlugin->getAuthItem($name);
    }

    public function getAuthItems($type = null, $userId = null) {
        return $this->authorizationPlugin->getAuthItems($type, $userId);
    }

    public function getItemChildren($itemName) {
        return $this->authorizationPlugin->getItemChildren($itemName);
    }

    public function hasItemChild($itemName, $childName) {
        return $this->authorizationPlugin->hasItemChild($itemName, $childName);
    }

    public function isAssigned($itemName, $userId) {
        return $this->authorizationPlugin->isAssigned($itemName, $userId);
    }

    public function removeAuthItem($name) {
        return $this->authorizationPlugin->removeAuthItem($name);
    }

    public function removeItemChild($itemName, $childName) {
        return $this->authorizationPlugin->removeItemChild($itemName, $childName);
    }

    public function revoke($itemName, $userId) {
        return $this->authorizationPlugin->revoke($itemName, $userId);
    }

    public function save() {
        return $this->authorizationPlugin->save();
    }

    public function saveAuthAssignment($assignment) {
        return $this->authorizationPlugin->saveAuthAssignment($assignment);
    }

    public function saveAuthItem($item, $oldName = null) {
        return $this->authorizationPlugin->saveAuthItem($item);
    }

}