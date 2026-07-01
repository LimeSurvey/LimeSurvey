<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_626 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up(): void
    {
        $boxes = $this->db->createCommand()
            ->select("*")
            ->from("{{boxes}}")
            ->order('position ASC')
            ->queryAll();

        if (!empty($boxes)) {
            if ($boxes[array_key_first($boxes)]['position'] === 1) {
                // we increase the position of all boxes by 1
                foreach ($boxes as $box) {
                    $this->db->createCommand()->update(
                        '{{boxes}}',
                        ['position' => $box['position'] + 1],
                        "id = '{$box['id']}'"
                    );
                }
            }

            foreach ($boxes as $box) {
                // Remove the old List surveys box
                if (
                    $box['url'] === 'dashboard/view' ||
                    $box['url'] === 'surveyAdministration/listsurveys'
                ) {
                    $this->db->createCommand()
                        ->delete('{{boxes}}', 'url=:url', [':url' => $box['url']]);
                }
            }

            // Then we recreate them
            $oDB = App()->db;
            $oDB->createCommand()->insert('{{boxes}}', [
                'position'   => '1',
                'url'        => 'dashboard/view',
                'title'      => gT('Dashboard'),
                'ico'        => 'ri-function-fill',
                'desc'       => gT('View dashboard'),
                'page'       => 'welcome',
                'usergroup'  => '-1',
                'buttontext' => gT('View dashboard')
            ]);
        }
    }
}
