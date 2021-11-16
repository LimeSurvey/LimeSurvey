            dropColumn('{{sessions}}', 'data');
            addColumn('{{sessions}}', 'data', 'binary');

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->setGlobalOption("ajaxmode", "off");
            }

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 352], "stg_name='DBVersion'");
            $oTransaction->commit();
