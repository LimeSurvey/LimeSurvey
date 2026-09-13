<?php

namespace LimeSurvey\Helpers\Update;

class Update_713 extends DatabaseUpdateBase
{
    /**
     * Strips the redundant, config-derived type-directory prefix from custom question theme xml_path values.
     * @inheritDoc
     */
    public function up()
    {
        $themeRootDir = rtrim(str_replace('\\', '/', (string) \Yii::app()->getConfig('userquestionthemerootdir')), '/');

        $rows = $this->db->createCommand()
            ->select('id, xml_path')
            ->from('{{question_themes}}')
            ->where('core_theme = :core_theme', [':core_theme' => 0])
            ->queryAll();

        foreach ($rows as $row) {
            $xmlPath = str_replace('\\', '/', (string) $row['xml_path']);
            if (strncmp($xmlPath, $themeRootDir . '/', strlen($themeRootDir) + 1) === 0) {
                $relativePath = substr($xmlPath, strlen($themeRootDir) + 1);
                $this->db->createCommand()->update(
                    '{{question_themes}}',
                    ['xml_path' => $relativePath],
                    'id = :id',
                    [':id' => $row['id']]
                );
            }
        }
    }
}
