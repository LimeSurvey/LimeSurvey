<?php

namespace LimeSurvey\Helpers\Update;

class Update_714 extends DatabaseUpdateBase
{
    /**
     * Strips the redundant, config-derived type-directory prefix from custom question theme image_path values.
     * @inheritDoc
     */
    public function up()
    {
        $themeRootDir = rtrim(str_replace('\\', '/', (string) \Yii::app()->getConfig('userquestionthemerootdir')), '/');

        $rows = $this->db->createCommand()
            ->select('id, image_path')
            ->from('{{question_themes}}')
            ->where('core_theme = :core_theme', [':core_theme' => 0])
            ->queryAll();

        foreach ($rows as $row) {
            $imagePath = str_replace('\\', '/', (string) $row['image_path']);
            $pos = strpos($imagePath, $themeRootDir);
            if ($pos === false) {
                continue;
            }
            $relativePath = ltrim(substr($imagePath, $pos + strlen($themeRootDir)), '/');
            $this->db->createCommand()->update(
                '{{question_themes}}',
                ['image_path' => $relativePath],
                'id = :id',
                [':id' => $row['id']]
            );
        }
    }
}
