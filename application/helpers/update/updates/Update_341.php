
            $oDB->createCommand()->truncateTable('{{tutorials}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');


            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 341), "stg_name='DBVersion'");
            $oTransaction->commit();
