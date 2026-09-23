<?php

namespace LimeSurvey\Helpers\Update;

class Update_718 extends DatabaseUpdateBase
{
    /**
     * "Email", "Full name", "Created on" and "Created by" used to be permanently-visible
     * columns in the User management grid and could never be excluded via its column
     * selector. They are now optional/deselectable, so a column preference saved before
     * this change would not list them - and CLSGridView would then treat their absence
     * as "the user deselected these" and hide them. Merge them into every existing
     * preference once.
     */
    #[\Override]
    public function up()
    {
        $legacyColumns = ['email', 'full_name', 'created', 'search_parentUserName'];
        $settingName = 'gridview_columns_usermanagement--identity-gridPanel';

        $rows = $this->db->createCommand()
            ->select('id, stg_value')
            ->from('{{settings_user}}')
            ->where('stg_name = :name', [':name' => $settingName])
            ->queryAll();

        foreach ($rows as $row) {
            $columnsSelected = json_decode((string) $row['stg_value'], true);
            if (!is_array($columnsSelected)) {
                continue;
            }
            $merged = array_values(array_unique(array_merge($columnsSelected, $legacyColumns)));
            $this->db->createCommand()->update(
                '{{settings_user}}',
                ['stg_value' => json_encode($merged)],
                'id = :id',
                [':id' => $row['id']]
            );
        }
    }
}
