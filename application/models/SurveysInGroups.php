<?php 
/**
 * Class SurveysGroups
 * @inheritdoc
 * Used for Permission on survey inside group : 
 *
 */
class UserInGroup extends SurveysGroups
{

    /**
     * Get Permission data for Permission object
     * @param string $key
     * @return array
     */
    public static function getPermissionData($key = null)
    {
        $aPermission = array(
            'sureys' => array(
                'create' => false,
                'update' => true,
                'delete' => true,
                'import' => false,
                'export' => true,
                'title' => gT("Sureys in this group"),
                'description' => gT("Permission on surveys in this group."),
                'img' => ' fa fa-edit',
            ),
        );
        if ($key) {
            if(isset($aPermission[$key])) {
                return $aPermission[$key];
            }
            return null;
        }
        return $aPermission;
    }

    /**
     * Get the owner id of this Survey group
     * Used for Permission
     * @return integer|null
     */
    public function getOwnerId()
    {
        if(!App()->getConfig('ownerManageAllSurveysInGroup')) {
            return null;
        }
        return $this->owner_id;
    }

}
