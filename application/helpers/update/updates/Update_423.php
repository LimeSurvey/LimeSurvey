<?php

namespace LimeSurvey\Helpers\Update;

class Update_423 extends DatabaseUpdateBase
{
    public function up()
    {
            //update core themes api_version
            $this->db->createCommand()->update(
                '{{templates}}',
                array(
                    'api_version' => "3.0",
                    'version' => "3.0",
                    'copyright' => "Copyright (C) 2007-2026 The LimeSurvey Project Team\r\nAll rights reserved."
                ),
                "name='fruity'"
            );
            $this->db->createCommand()->update(
                '{{templates}}',
                array(
                    'api_version' => "3.0",
                    'version' => "3.0",
                    'copyright' => "Copyright (C) 2007-2026 The LimeSurvey Project Team\r\nAll rights reserved."
                ),
                "name='vanilla'"
            );
            $this->db->createCommand()->update(
                '{{templates}}',
                array(
                    'api_version' => "3.0",
                    'version' => "3.0",
                    'copyright' => "Copyright (C) 2007-2026 The LimeSurvey Project Team\r\nAll rights reserved."
                ),
                "name='bootwatch'"
            );
    }
}
