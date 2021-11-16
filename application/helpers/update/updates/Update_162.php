            // Fix participant db types
            alterColumn('{{participant_attribute}}', 'value', "text", false);
            alterColumn('{{participant_attribute_names_lang}}', 'attribute_name', "string(255)", false);
            alterColumn('{{participant_attribute_values}}', 'value', "text", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 162), "stg_name='DBVersion'");
            $oTransaction->commit();
