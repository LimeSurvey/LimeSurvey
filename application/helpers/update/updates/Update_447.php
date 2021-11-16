
            $oDB->createCommand()->addColumn('{{users}}', 'validation_key', 'string(38)');
            $oDB->createCommand()->addColumn('{{users}}', 'validation_key_expiration', 'datetime');

            //override existing email text (take out password there)
            $sqlGetAdminCreationEmailTemplat = "SELECT stg_value FROM {{settings_global}} WHERE stg_name='admincreationemailtemplate'";
            $adminCreationEmailTemplateValue = $oDB->createCommand($sqlGetAdminCreationEmailTemplat)->queryAll();
            if ($adminCreationEmailTemplateValue) {
                if ($adminCreationEmailTemplateValue[0]['stg_value'] === null || $adminCreationEmailTemplateValue[0]['stg_value'] === '') {
                    // if text in admincreationemailtemplate is empty use the default from LsDafaultDataSets
                    $defaultCreationEmailContent = LsDefaultDataSets::getDefaultUserAdministrationSettings();
                    $replaceValue = $defaultCreationEmailContent['admincreationemailtemplate'];
                } else { // if not empty replace PASSWORD with *** and write it back to DB
                    $replaceValue = str_replace('PASSWORD', '***', $adminCreationEmailTemplateValue[0]['stg_value']);
                }
                $oDB->createCommand()->update(
                    '{{settings_global}}',
                    array('stg_value' => $replaceValue),
                    "stg_name='admincreationemailtemplate'"
                );
            }

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 447), "stg_name='DBVersion'");
            $oTransaction->commit();
