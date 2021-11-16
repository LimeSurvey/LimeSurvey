            $oDB->createCommand()->delete('{{surveymenu_entries}}', 'name=:name', [':name' => 'reorder']);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 417), "stg_name='DBVersion'");
            $oTransaction->commit();
