<?php

namespace LimeSurvey\Helpers\Update;

class Update_421 extends DatabaseUpdateBase
{
    /**
     * Create the question_themes table and fill it with the core question themes.
     *
     * @return void
     */
    #[\Override]
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
            // The Gender question type was removed from the default data set. Later updates
            // still rely on its theme until Update_720 converts its questions and removes it.
            $baseQuestionThemeEntries[] = $this->getLegacyGenderThemeEntry();
        foreach ($baseQuestionThemeEntries as $baseQuestionThemeEntry) {
            $this->db->createCommand()->insert("{{question_themes}}", $baseQuestionThemeEntry);
        }
            unset($baseQuestionThemeEntries);
    }

    /**
     * Get the question theme entry of the removed Gender question type.
     *
     * @return array
     */
    private function getLegacyGenderThemeEntry(): array
    {
        return array(
            "name" => "gender",
            "visible" => "Y",
            "xml_path" => "application/views/survey/questions/answer/gender",
            "image_path" => "/assets/images/screenshots/G.png",
            "title" => "Gender",
            "creation_date" => "2018-09-08 00:00:00",
            "author" => "LimeSurvey GmbH",
            "author_email" => "info@limesurvey.org",
            "author_url" => "http://www.limesurvey.org",
            "copyright" => "Copyright (C) 2005 - 2018 LimeSurvey Gmbh, Inc. All rights reserved.",
            "license" => "GNU General Public License version 2 or later",
            "version" => "1.0",
            "api_version" => "1",
            "description" => "Gender question type configuration",
            "last_update" => "2019-09-23 15:05:59",
            "owner_id" => 1,
            "theme_type" => "question_theme",
            "question_type" => "G",
            "core_theme" => 1,
            "extends" => "",
            "group" => "Mask questions",
            "settings" => "{\"subquestions\":\"0\",\"answerscales\":\"0\",\"hasdefaultvalues\":\"0\",\"assessable\":\"0\",\"class\":\"gender\"}",
        );
    }
}
