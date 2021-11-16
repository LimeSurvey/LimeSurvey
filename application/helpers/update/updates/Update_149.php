            $aFields = array(
                'id' => 'integer',
                'sid' => 'integer',
                'parameter' => 'string(50)',
                'targetqid' => 'integer',
                'targetsqid' => 'integer'
            );
            $oDB->createCommand()->createTable('{{survey_url_parameters}}', $aFields);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 149), "stg_name='DBVersion'");
            $oTransaction->commit();
