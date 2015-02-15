<?php
namespace ls\core\plugins;
use ls\pluginmanager\PluginBase;
use \ls\pluginmanager\PluginEvent;
use \Yii;

class PermissionDb extends PluginBase implements \ls\pluginmanager\iAuthorizationPlugin 
{
    protected $storage = 'DbStorage';
    
    public function addItemChild($itemName, $childName) {
        
    }

    public function assign($itemName, $userId, $bizRule = null, $data = null) {
        
    }

    public function checkAccess($itemName, $userId, $params = array()) {
        $defaults = [
            'entity' => 'global',
            'entity_id' => 0,
            'crud' => 'read'
        ];
        $params = array_merge($defaults, $params);
        // Check superadmin first.
        $superAdmin = ($itemName != 'superadmin') && $this->checkAccess('superadmin', $userId);
        return $superAdmin ?: \Permission::model()->hasPermission($params['entity_id'], $params['entity'], $itemName, $params['crud'], $userId);
    }

    public function clearAll() {
        
    }

    public function clearAuthAssignments() {
        
    }

    public function createAuthItem($name, $type, $description = '', $bizRule = null, $data = null) {
        
    }

    public function executeBizRule($bizRule, $params, $data) {
        
    }

    public function getAuthAssignment($itemName, $userId) {
        
    }

    public function getAuthAssignments($userId) {
        
    }

    public function getAuthItem($name) {
        
    }

    public function getAuthItems($type = null, $userId = null) {
        
    }

    public function getItemChildren($itemName) {
        
    }

    public function hasItemChild($itemName, $childName) {
        
    }

    public function isAssigned($itemName, $userId) {
        
    }

    public function removeAuthItem($name) {
        
    }

    public function removeItemChild($itemName, $childName) {
        
    }

    public function revoke($itemName, $userId) {
        
    }

    public function save() {
        
    }

    public function saveAuthAssignment($assignment) {
        
    }

    public function saveAuthItem($item, $oldName = null) {
        
    }

}