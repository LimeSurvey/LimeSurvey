<?php

namespace LimeSurvey\Helpers\Update;

class Update_719 extends DatabaseUpdateBase
{
    /**
     * Align the question theme "hasdefaultvalues" setting with the core question types:
     *  - enable default answers for 5 point choice ('5'), which new installations did not get
     *  - disable default answers for Gender ('G'), which Update_615 had enabled
     */
    #[\Override]
    public function up()
    {
        $this->setHasDefaultValues('5', '1');
        $this->setHasDefaultValues('G', '0');
    }

    /**
     * Set "hasdefaultvalues" in the settings of all question themes of a question type.
     *
     * @param string $questionType One-character question type
     * @param string $value "1" to enable default answers, "0" to disable them
     * @return void
     */
    private function setHasDefaultValues(string $questionType, string $value)
    {
        $themes = $this->db->createCommand(
            "SELECT id, settings FROM {{question_themes}} WHERE question_type = :question_type"
        )->queryAll(true, [':question_type' => $questionType]);

        foreach ($themes as $row) {
            $settings = json_decode($row['settings'] ?? '{}', true);
            if (!is_array($settings)) {
                $settings = [];
            }
            $settings['hasdefaultvalues'] = $value;
            $this->db->createCommand()->update(
                '{{question_themes}}',
                ['settings' => json_encode($settings)],
                'id = :id',
                [':id' => $row['id']]
            );
        }
    }
}
