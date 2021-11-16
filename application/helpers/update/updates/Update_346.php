            createSurveyMenuTable($oDB);
            $oDB->createCommand()->truncateTable('{{tutorials}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 346], "stg_name='DBVersion'");
            $oTransaction->commit();
