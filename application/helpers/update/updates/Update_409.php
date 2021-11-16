            $oTransaction = $oDB->beginTransaction();

            $sEncrypted = 'N';
            $oDB->createCommand()->update(
                '{{participant_attribute_names}}',
                array('encrypted' => $sEncrypted),
                "core_attribute='Y'"
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 409), "stg_name='DBVersion'");
            $oTransaction->commit();
