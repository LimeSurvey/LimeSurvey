<?php

namespace LimeSurvey\Helpers\Update;

class Update_160 extends DatabaseUpdateBase
{
    public function run()
    {
            alterLanguageCode('it', 'it-informal');
            alterLanguageCode('it-formal', 'it');
    }
}