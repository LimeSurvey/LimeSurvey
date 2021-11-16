            $oTransaction = $oDB->beginTransaction();
            //refactore controller assessment (surveymenu_entry link changes to new controller rout)
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'assessment/index',
                ),
                "name='assessments'"
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 437), "stg_name='DBVersion'");
            $oTransaction->commit();
