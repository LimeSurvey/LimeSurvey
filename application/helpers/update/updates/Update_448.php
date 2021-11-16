            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand('UPDATE {{question_themes}} SET settings=\'{"subquestions":"1","answerscales":"2","hasdefaultvalues":"0","assessable":"1","class":"array-flexible-dual-scale"}\' WHERE name=\'arrays/dualscale\'')->execute();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 448), "stg_name='DBVersion'");
            $oTransaction->commit();
