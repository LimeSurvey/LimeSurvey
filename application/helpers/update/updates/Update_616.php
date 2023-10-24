<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_616 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $this->db->createCommand()->addColumn('{{users}}', 'status', 'BOOLEAN DEFAULT TRUE');
//
//        $users = \Yii::app()->db->createCommand("SELECT * FROM {{users}}")->queryAll();
//        foreach ($users as $user) {
//            if (isset($user['status'])) {
//                $this->db->createCommand()->update('{{users}}', ['status' => 0], 'uid = :uid AND expires <= CURRENT_TIME()', ['uid' => $user['uid']]);
//            }
//        }
    }
}
