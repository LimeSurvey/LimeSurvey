<?php

namespace LimeSurvey\Helpers\Update;

class Update_634 extends DatabaseUpdateBase
{
    public function up()
    {
        $other_settings_inherit = [
            'question_code_prefix' => 'I',
            'subquestion_code_prefix' => 'I',
            'answer_code_prefix' => 'I'
        ];

        // Get all surveys with non-null othersettings
        $surveys = $this->db->createCommand()
            ->select(['sid', 'othersettings'])
            ->from('{{surveys}}')
            ->queryAll();

        // Process each survey
        foreach ($surveys as $survey) {
            $settings = $survey['othersettings'];

            if ($settings === null) {
                $this->updateSettings($other_settings_inherit, $survey);
                continue;
            }

            $settings = json_decode($settings, true);
            if (!is_array($settings)) {
                $this->updateSettings($other_settings_inherit, $survey);
                continue;
            }

            // Check and update empty prefix values
            foreach (['question_code_prefix', 'subquestion_code_prefix', 'answer_code_prefix'] as $prefixKey) {
                if (isset($settings[$prefixKey]) && $settings[$prefixKey] === '') {
                    $settings[$prefixKey] = 'I';
                }
            }
            $this->updateSettings($settings, $survey);
        }
    }

    function updateSettings($settings, $survey)
    {
        $this->db->createCommand()->update(
            '{{surveys}}',
            ['othersettings' => json_encode($settings)],
            'sid = :sid',
            [':sid' => $survey['sid']]
        );
    }
}
