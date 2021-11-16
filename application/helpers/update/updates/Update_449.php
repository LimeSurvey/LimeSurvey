
            //updating the default values for htmleditor
            //surveys_groupsettings htmlemail should be 'Y'
            alterColumn('{{surveys_groupsettings}}', 'htmlemail', 'string(1)', false, 'Y');
            alterColumn('{{surveys}}', 'htmlemail', 'string(1)', false, 'Y');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 449), "stg_name='DBVersion'");
            $oTransaction->commit();
