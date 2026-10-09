<?php

namespace LimeSurvey\Helpers\Update;

class Update_160 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            alterLanguageCode('it', 'it-informal');
            alterLanguageCode('it-formal', 'it');
    }
}
