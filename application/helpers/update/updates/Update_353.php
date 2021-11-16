
            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->addOptionFromXMLToLiveTheme();
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 353], "stg_name='DBVersion'");
            $oTransaction->commit();
