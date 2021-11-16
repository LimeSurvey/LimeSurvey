            $aFields = array(
                'id' => 'integer',
                'sid' => 'integer',
                'parameter' => 'string(50)',
                'targetqid' => 'integer',
                'targetsqid' => 'integer'
            );
            $oDB->createCommand()->createTable('{{survey_url_parameters}}', $aFields);
