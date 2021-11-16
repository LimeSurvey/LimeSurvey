            $oTransaction = $oDB->beginTransaction();
            LimeExpressionManager::UpgradeConditionsToRelevance();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 158), "stg_name='DBVersion'");
            $oTransaction->commit();
