<?php

namespace LimeSurvey\Helpers\Update;

class Update_421 extends DatabaseUpdateBase
{
    public function up()
    {
            // question_themes
            $this->db->createCommand()->createTable(
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
                $this->options
            );

            $this->db->createCommand()->createIndex('{{idx1_question_themes}}', '{{question_themes}}', 'name', false);

            $baseQuestionThemeEntries = \LsDefaultDataSets::getBaseQuestionThemeEntries();
        foreach ($baseQuestionThemeEntries as $baseQuestionThemeEntry) {
            $this->db->createCommand()->insert("{{question_themes}}", $baseQuestionThemeEntry);
        }
            unset($baseQuestionThemeEntries);
    }
}
