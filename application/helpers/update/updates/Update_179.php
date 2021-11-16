            $oTransaction = $oDB->beginTransaction();
            upgradeSurveys177(); // Needs to be run again to make sure
            upgradeTokenTables179();
            alterColumn('{{participants}}', 'email', "string(254)", false);
            alterColumn('{{participants}}', 'firstname', "string(150)", false);
            alterColumn('{{participants}}', 'lastname', "string(150)", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 179), "stg_name='DBVersion'");
            $oTransaction->commit();
