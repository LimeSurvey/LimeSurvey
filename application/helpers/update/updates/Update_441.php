<?php

namespace LimeSurvey\Helpers\Update;

class Update_441 extends DatabaseUpdateBase
{
    public function up()
    {
            // Convert old html editor modes if present in global settings
            $this->db->createCommand()->update(
                '{{settings_global}}',
                array(
                    'stg_value' => 'inline',
                ),
                "stg_name='defaulthtmleditormode' AND stg_value='wysiwyg'"
            );
            $this->db->createCommand()->update(
                '{{settings_global}}',
                array(
                    'stg_value' => 'none',
                ),
                "stg_name='defaulthtmleditormode' AND stg_value='source'"
            );
            // Convert old html editor modes if present in profile settings
            $this->db->createCommand()->update(
                '{{users}}',
                array(
                    'htmleditormode' => 'inline',
                ),
                "htmleditormode='wysiwyg'"
            );
            $this->db->createCommand()->update(
                '{{users}}',
                array(
                    'htmleditormode' => 'none',
                ),
                "htmleditormode='source'"
            );
    }
}
