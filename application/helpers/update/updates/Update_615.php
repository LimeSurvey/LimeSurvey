<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_615 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $questionThemes = $this->getQuestionThemes();
        if (!empty($questionThemes)) {
            foreach ($questionThemes as $theme) {
                if (!empty($theme['settings'])) {
                    $sSettingsJson = $theme['settings'];
                    $oSettings = json_decode($sSettingsJson);
                    $oSettings->hasdefaultvalues = "1";
                    $oNewSettingsJson = json_encode($oSettings);
                    $this->db->createCommand()->update(
                        '{{question_themes}}',
                        ['settings' => $oNewSettingsJson],
                        'id = :id',
                        [':id' => $theme['id']]
                    );
                }
            }
        }
    }

    public function getQuestionThemes()
    {
        return $this->db->createCommand()
            ->select('id, name, settings')
            ->from('{{question_themes}}')
            ->where(['in', 'name', ['5pointchoice', 'gender']])
            ->queryAll();
    }
}
