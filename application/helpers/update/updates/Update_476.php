<?php

namespace LimeSurvey\Helpers\Update;

use Yii;
use SurveyThemeHelper;

class Update_476 extends DatabaseUpdateBase
{
    public function up()
    {
        Yii::import('application.helpers.SurveyThemeHelper');
        $templateConfigurations = $this->db->createCommand()->select(['id', 'template_name', 'sid', 'options'])->from('{{template_configuration}}')->queryAll();
        if (!empty($templateConfigurations)) {
            foreach ($templateConfigurations as $templateConfiguration) {
                $decodedOptions = json_decode((string) $templateConfiguration['options'], true);
                if (is_array($decodedOptions)) {
                    foreach ($decodedOptions as &$value) {
                        $value = SurveyThemeHelper::sanitizePathInOption($value, $templateConfiguration['template_name'], $templateConfiguration['sid']);
                    }
                    $sanitizedOptions = json_encode($decodedOptions);
                    $this->db->createCommand()->update('{{template_configuration}}', ['options' => $sanitizedOptions], 'id=:id', [':id' => $templateConfiguration['id']]);
                }
            }
        }
    }
}
