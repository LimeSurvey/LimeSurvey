
            $oDB->createCommand()->addColumn('{{users}}', 'last_forgot_email_password', 'datetime');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 472), "stg_name='DBVersion'");
            $oTransaction->commit();
