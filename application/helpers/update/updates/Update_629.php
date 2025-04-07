<?php

namespace LimeSurvey\Helpers\Update;

class Update_629 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        if (!tableExists('{{twoFactorUsers}}')) {
            return;
        }

        // Rename the twoFactorUsers table
        $this->db->createCommand()->renameTable('{{twoFactorUsers}}', '{{user_mfa_settings}}');

        // Update authType
        // Any type other than 'yubikey' will be set to 'totp'
        $this->db->createCommand()->update(
            '{{user_mfa_settings}}',
            ['authType' => 'totp'],
            "authType != 'yubi'"
        );

        // Encrypt the secretKey
        $tfaUserKeys = $this->db->createCommand()
            ->select('*')
            ->from('{{user_mfa_settings}}')
            ->queryAll();
        foreach ($tfaUserKeys as $tfaUserKey) {
            if (empty($tfaUserKey['secretKey'])) {
                continue;
            }
            $encryptedSecretKey = \LSActiveRecord::encryptSingle($tfaUserKey['secretKey']);
            $this->db->createCommand()->update(
                '{{user_mfa_settings}}',
                ['secretKey' => $encryptedSecretKey],
                'uid = :uid',
                ['uid' => $tfaUserKey['uid']]
            );
        }
    }
}
