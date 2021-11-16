<?php

namespace LimeSurvey\Helpers\Update;

class Update_442 extends DatabaseUpdateBase
{
    public function run()
    {
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
    }
}