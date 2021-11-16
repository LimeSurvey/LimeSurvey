            $oDB->createCommand()->update(
                '{{participant_attribute_names}}',
                array('encrypted' => 'Y'),
                "core_attribute='Y'"
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 408), "stg_name='DBVersion'");
            $oTransaction->commit();
