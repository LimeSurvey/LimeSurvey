            dropColumn('{{sessions}}', 'data');
            addColumn('{{sessions}}', 'data', 'binary');

            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->setGlobalOption("ajaxmode", "off");
            }

