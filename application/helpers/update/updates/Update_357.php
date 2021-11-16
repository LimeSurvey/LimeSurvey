            //// IKI
            $oDB->createCommand()->renameColumn('{{surveys_groups}}', 'owner_uid', 'owner_id');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 357], "stg_name='DBVersion'");
            $oTransaction->commit();
