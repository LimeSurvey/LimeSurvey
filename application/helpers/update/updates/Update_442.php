            $questionTheme = new QuestionTheme();
            $questionsMetaData = $questionTheme->getAllQuestionMetaData(false, false, true)['available_themes'];
            foreach ($questionsMetaData as $questionMetaData) {
                $oDB->createCommand()->update(
                    '{{question_themes}}',
                    ['image_path' => $questionMetaData['image_path']],
                    "name = :name AND extends = :extends AND theme_type = :type",
                    [
                        "name" => $questionMetaData['name'],
                        "extends" => $questionMetaData['questionType'],
                        "type" => $questionMetaData['type']
                    ]
                );
            }
            $oDB->createCommand()->insert(
                "{{plugins}}",
                [
                    'name' => 'TwoFactorAdminLogin',
                    'plugin_type' => 'core',
                    'active' => 0,
                    'version' => '1.2.5',
                    'load_error' => 0,
                    'load_error_message' => null
                ]
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 442), "stg_name='DBVersion'");
            $oTransaction->commit();
