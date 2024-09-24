<?php

namespace LimeSurvey\Helpers\Update;

class Update_452 extends DatabaseUpdateBase
{
    public function up()
    {

            // When encryptionkeypair is empty, encryption was never used (user comes from LS3), so it's safe to skip this udpate.
        if (!empty(\Yii::app()->getConfig('encryptionkeypair'))) {
            // update encryption for smtppassword
            $emailsmtppassword = $this->db->createCommand()
                ->select('*')
                ->from('{{settings_global}}')
                ->where('stg_name = :stg_name', ['stg_name' => 'emailsmtppassword'])
                ->queryRow();
            if ($emailsmtppassword && !empty($emailsmtppassword['stg_value']) && $emailsmtppassword['stg_value'] !== 'somepassword') {
                $decryptedValue = \LSActiveRecord::decryptSingleOld($emailsmtppassword['stg_value']);
                $encryptedValue = \LSActiveRecord::encryptSingle($decryptedValue);
                $this->db->createCommand()->update('{{settings_global}}', ['stg_value' => $encryptedValue ?? ''], "stg_name='emailsmtppassword'");
            }

            // update encryption for bounceaccountpass
            $bounceaccountpass = $this->db->createCommand()
                ->select('*')
                ->from('{{settings_global}}')
                ->where('stg_name = :stg_name', ['stg_name' => 'bounceaccountpass'])
                ->queryRow();
            if ($bounceaccountpass && !empty($bounceaccountpass['stg_value']) && $bounceaccountpass['stg_value'] !== 'enteredpassword') {
                $decryptedValue = \LSActiveRecord::decryptSingleOld($bounceaccountpass['stg_value']);
                $encryptedValue = \LSActiveRecord::encryptSingle($decryptedValue);
                $this->db->createCommand()->update('{{settings_global}}', ['stg_value' => $encryptedValue ?? ''], "stg_name='bounceaccountpass'");
            }
        }
    }
}
