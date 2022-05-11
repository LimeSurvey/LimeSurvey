<?php

namespace LimeSurvey\Helpers\Update;

use User;

class Update_180 extends DatabaseUpdateBase
{
    public function up()
    {
        $aUsers = User::model()->findAll();
        $aPerm = array(
            'entity_id' => 0,
            'entity' => 'global',
            'uid' => 0,
            'permission' => 'auth_db',
            'create_p' => 0,
            'read_p' => 1,
            'update_p' => 0,
            'delete_p' => 0,
            'import_p' => 0,
            'export_p' => 0
        );

        foreach ($aUsers as $oUser) {
            $permissionExists = $this->db->createCommand()->select('id')->from("{{permissions}}")->where(
                "(permission='auth_db' OR permission='superadmin') and read_p=1 and entity='global' and uid=:uid",
                [':uid' => $oUser->uid]
            )->queryScalar();
            if ($permissionExists == false) {
                $newPermission = $aPerm;
                $newPermission['uid'] = $oUser->uid;
                $this->db->createCommand()->insert("{{permissions}}", $newPermission);
            }
        }
    }
}
