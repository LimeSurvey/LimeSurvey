<?php

namespace LimeSurvey\Helpers\Update;

class Update_629 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $tfaUserKeys = $this->db->createCommand()
            ->select('*')
            ->from('{{twoFactorUsers}}')
            ->queryAll();
        foreach ($tfaUserKeys as $tfaUserKey) {
            if (empty($tfaUserKey['secretKey'])) {
                continue;
            }
            $encryptedSecretKey = \LSActiveRecord::encryptSingle($tfaUserKey['secretKey']);
            $this->db->createCommand()
                ->update(
                    '{{twoFactorUsers}}',
                    ['secretKey' => $encryptedSecretKey],
                    'uid = :uid',
                    ['uid' => $tfaUserKey['uid']]
                );
        }
        $a = 1;
    }
}
