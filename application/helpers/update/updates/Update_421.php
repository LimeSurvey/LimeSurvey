            $oTransaction = $oDB->beginTransaction();
            // question_themes
            $oDB->createCommand()->createTable(
                '{{question_themes}}',
                [
                    'id' => "pk",
                    'name' => "string(150) NOT NULL",
                    'visible' => "string(1) NULL",
                    'xml_path' => "string(255) NULL",
                    'image_path' => 'string(255) NULL',
                    'title' => "string(100) NOT NULL",
                    'creation_date' => "datetime NULL",
                    'author' => "string(150) NULL",
                    'author_email' => "string(255) NULL",
                    'author_url' => "string(255) NULL",
                    'copyright' => "text",
                    'license' => "text",
                    'version' => "string(45) NULL",
                    'api_version' => "string(45) NOT NULL",
                    'description' => "text",
                    'last_update' => "datetime NULL",
                    'owner_id' => "integer NULL",
                    'theme_type' => "string(150)",
                    'question_type' => "string(150) NOT NULL",
                    'core_theme' => 'boolean',
                    'extends' => "string(150) NULL",
                    'group' => "string(150)",
                    'settings' => "text"
                ],
                $options
            );

            $oDB->createCommand()->createIndex('{{idx1_question_themes}}', '{{question_themes}}', 'name', false);

            $baseQuestionThemeEntries = LsDefaultDataSets::getBaseQuestionThemeEntries();
            foreach ($baseQuestionThemeEntries as $baseQuestionThemeEntry) {
                $oDB->createCommand()->insert("{{question_themes}}", $baseQuestionThemeEntry);
            }
            unset($baseQuestionThemeEntries);

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 421), "stg_name='DBVersion'");
            $oTransaction->commit();
