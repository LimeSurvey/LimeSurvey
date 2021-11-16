
            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->addOptionFromXMLToLiveTheme();
            }

