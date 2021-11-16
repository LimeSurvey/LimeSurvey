
            $aTHemes = TemplateConfiguration::model()->findAll();

            foreach ($aTHemes as $oTheme) {
                $oTheme->setGlobalOption("ajaxmode", "on");
            }

