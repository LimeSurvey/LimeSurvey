<?php

namespace LimeSurvey\Helpers\Update;

class Update_436 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            $this->db->createCommand()->update('{{boxes}}', array('url' => 'themeOptions'), "url='admin/themeoptions'");
    }
}
