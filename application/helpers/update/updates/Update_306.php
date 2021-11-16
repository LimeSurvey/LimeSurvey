            createSurveyGroupTables306($oDB);
            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 306), "stg_name='DBVersion'");
