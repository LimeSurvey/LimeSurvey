            Yii::import('application.helpers.SurveyThemeHelper');
            $templateConfigurations = $oDB->createCommand()->select(['id', 'template_name', 'sid', 'options'])->from('{{template_configuration}}')->queryAll();
            if (!empty($templateConfigurations)) {
                foreach ($templateConfigurations as $templateConfiguration) {
                    $decodedOptions = json_decode($templateConfiguration['options'], true);
                    if (is_array($decodedOptions)) {
                        foreach ($decodedOptions as &$value) {
                            $value = SurveyThemeHelper::sanitizePathInOption($value, $templateConfiguration['template_name'], $templateConfiguration['sid']);
                        }
                        $sanitizedOptions = json_encode($decodedOptions);
                        $oDB->createCommand()->update('{{template_configuration}}', ['options' => $sanitizedOptions], 'id=:id', [':id' => $templateConfiguration['id']]);
                    }
                }
            }

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 476), "stg_name='DBVersion'");
            $oTransaction->commit();
