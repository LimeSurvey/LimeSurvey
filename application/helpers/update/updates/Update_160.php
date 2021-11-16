<?php

namespace LimeSurvey\Helpers\Update;

class Update_160 extends DatabaseUpdateBase
{
    public function up()
    {
            alterLanguageCode('it', 'it-informal');
            alterLanguageCode('it-formal', 'it');
    }
}
