            addColumn('{{surveys_languagesettings}}', 'surveyls_dateformat', "integer NOT NULL default 1");
            addColumn('{{users}}', 'dateformat', "integer NOT NULL default 1");
            $oDB->createCommand()->update('{{surveys}}', array('startdate' => null), "usestartdate='N'");
            $oDB->createCommand()->update('{{surveys}}', array('expires' => null), "useexpiry='N'");
            dropColumn('{{surveys}}', 'useexpiry');
            dropColumn('{{surveys}}', 'usestartdate');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 137), "stg_name='DBVersion'");
            $oTransaction->commit();
