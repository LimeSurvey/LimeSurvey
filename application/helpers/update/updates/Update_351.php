            $oTransaction = $oDB->beginTransaction();

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->setGlobalOption("ajaxmode", "on");
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 351], "stg_name='DBVersion'");
            $oTransaction->commit();
