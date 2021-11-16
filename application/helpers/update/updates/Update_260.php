            $oTransaction = $oDB->beginTransaction();
            alterColumn('{{participant_attribute_names}}', 'defaultname', "string(255)", false);
            alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 260), "stg_name='DBVersion'");
            $oTransaction->commit();
