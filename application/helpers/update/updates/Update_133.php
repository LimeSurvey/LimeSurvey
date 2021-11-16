            $oTransaction = $oDB->beginTransaction();
            addColumn('{{users}}', 'one_time_pw', 'binary');
            // Add new assessment setting
            addColumn('{{surveys}}', 'assessments', "string(1) NOT NULL default 'N'");
            // add new assessment value fields to answers & labels
            addColumn('{{answers}}', 'assessment_value', "integer NOT NULL default '0'");
            addColumn('{{labels}}', 'assessment_value', "integer NOT NULL default '0'");
            // copy any valid codes from code field to assessment field
            switch (Yii::app()->db->driverName) {
                case 'mysql':
                    $oDB->createCommand(
                        "UPDATE {{answers}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'"
                    )->execute();
                    $oDB->createCommand(
                        "UPDATE {{labels}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'"
                    )->execute();
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    $oDB->createCommand(
                        "UPDATE {{assessments}} set message=concat(replace(message,'/''',''''),'<br /><a href=\"',link,'\">',link,'</a>')"
                    )->execute();
                    break;
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    try {
                        $oDB->createCommand(
                            "UPDATE {{answers}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1"
                        )->execute();
                        $oDB->createCommand(
                            "UPDATE {{labels}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1"
                        )->execute();
                    } catch (Exception $e) {
                    };
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    alterColumn('{{assessments}}', 'link', "text", false);
                    alterColumn('{{assessments}}', 'message', "text", false);
                    $oDB->createCommand(
                        "UPDATE {{assessments}} set message=replace(message,'/''','''')+'<br /><a href=\"'+link+'\">'+link+'</a>'"
                    )->execute();
                    break;
                case 'pgsql':
                    $oDB->createCommand(
                        "UPDATE {{answers}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'"
                    )->execute();
                    $oDB->createCommand(
                        "UPDATE {{labels}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'"
                    )->execute();
                    // copy assessment link to message since from now on we will have HTML assignment messages
                    $oDB->createCommand(
                        "UPDATE {{assessments}} set message=replace(message,'/''','''')||'<br /><a href=\"'||link||'\">'||link||'</a>'"
                    )->execute();
                    break;
            }
            // activate assessment where assessment rules exist
            $oDB->createCommand(
                "UPDATE {{surveys}} SET assessments='Y' where sid in (SELECT sid FROM {{assessments}} group by sid)"
            )->execute();
            // add language field to assessment table
            addColumn('{{assessments}}', 'language', "string(20) NOT NULL default 'en'");
            // update language field with default language of that particular survey
            $oDB->createCommand(
                "UPDATE {{assessments}} SET language=(select language from {{surveys}} where sid={{assessments}}.sid)"
            )->execute();
            // drop the old link field
            dropColumn('{{assessments}}', 'link');

            // Add new fields to survey language settings
            addColumn('{{surveys_languagesettings}}', 'surveyls_url', "string");
            addColumn('{{surveys_languagesettings}}', 'surveyls_endtext', 'text');
            // copy old URL fields ot language specific entries
            $oDB->createCommand(
                "UPDATE {{surveys_languagesettings}} set surveyls_url=(select url from {{surveys}} where sid={{surveys_languagesettings}}.surveyls_survey_id)"
            )->execute();
            // drop old URL field
            dropColumn('{{surveys}}', 'url');
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 133), "stg_name='DBVersion'");
            $oTransaction->commit();
