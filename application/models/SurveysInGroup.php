<?php 
/**
 * Class SurveysGroups
 * @inheritdoc
 * Used for Permission on survey inside group : 
 *
 */
class SurveysInGroup extends SurveysGroups
{
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveysGroups the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * Get Permission data for Permission object
     * @param string $key
     * @return array
     */
    public static function getPermissionData($key = null)
    {
        $aPermission = array(
            'surveys' => array(
                'create' => false,
                'read' => true,
                'update' => true,
                'delete' => true,
                'import' => false,
                'export' => true,
                'title' => gT("Sureys in this group"),
                'description' => gT("Permission on surveys in this group. To see the survey in list : read permission is checked and muts be set."),
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

    /**
     * @inheritdoc
     * No minimal permission : must be set or get it via owner (or global)
     */
    public static function getMinimalPermissionRead()
    {
        return null;
    }
}
