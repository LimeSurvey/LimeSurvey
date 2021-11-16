            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->addColumn(
                '{{surveys_groupsettings}}',
                'ipanonymize',
                "string(1) NOT NULL default 'N'"
            );
            $oDB->createCommand()->addColumn('{{surveys}}', 'ipanonymize', "string(1) NOT NULL default 'N'");

            //all groups (except default group gsid=0), must have inheritance value
            $oDB->createCommand()->update('{{surveys_groupsettings}}', array('ipanonymize' => 'I'), 'gsid<>0');

            //change gsid=1 for inheritance logic ...(redundant, but for better understanding and securit)
            $oDB->createCommand()->update('{{surveys_groupsettings}}', array('ipanonymize' => 'I'), 'gsid=1');

            //for all non active surveys,the value must be "I" for inheritance ...
            $oDB->createCommand()->update('{{surveys}}', array('ipanonymize' => 'I'), "active='N'");

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 426), "stg_name='DBVersion'");
            $oTransaction->commit();
