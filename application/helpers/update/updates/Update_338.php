<?php

namespace LimeSurvey\Helpers\Update;

class Update_338 extends DatabaseUpdateBase
{
    public function up()
    {
            $rowToRemove = $this->db->createCommand()->select("position, id")->from("{{boxes}}")->where(
                'ico=:ico',
                [':ico' => 'templates']
            )->queryRow();
            $position = 6;
        if ($rowToRemove !== false) {
            $this->db->createCommand()->delete("{{boxes}}", 'id=:id', [':id' => $rowToRemove['id']]);
            $position = $rowToRemove['position'];
        }
            $this->db->createCommand()->insert(
                "{{boxes}}",
                [
                    'position' => $position,
                    'url' => 'admin/themeoptions',
                    'title' => 'Themes',
                    'ico' => 'templates',
                    'desc' => 'Themes',
                    'page' => 'welcome',
                    'usergroup' => '-2'
                ]
            );
    }
}
