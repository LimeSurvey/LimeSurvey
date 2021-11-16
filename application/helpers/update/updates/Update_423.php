<?php

namespace LimeSurvey\Helpers\Update;

class Update_423 extends DatabaseUpdateBase
{
    public function run()
    {
            //update core themes api_version
            $oDB->createCommand()->update(
                '{{templates}}',
                array(
                    'api_version' => "3.0",
                    'version' => "3.0",
                    'copyright' => "Copyright (C) 2007-2019 The LimeSurvey Project Team\r\nAll rights reserved."
                ),
                "name='fruity'"
            );
            $oDB->createCommand()->update(
                '{{templates}}',
                array(
                    'api_version' => "3.0",
                    'version' => "3.0",
                    'copyright' => "Copyright (C) 2007-2019 The LimeSurvey Project Team\r\nAll rights reserved."
                ),
                "name='vanilla'"
            );
            $oDB->createCommand()->update(
                '{{templates}}',
                array(
                    'api_version' => "3.0",
                    'version' => "3.0",
                    'copyright' => "Copyright (C) 2007-2019 The LimeSurvey Project Team\r\nAll rights reserved."
                ),
                "name='bootwatch'"
            );
    }
}
