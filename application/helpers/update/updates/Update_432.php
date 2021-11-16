<?php

namespace LimeSurvey\Helpers\Update;

class Update_432 extends DatabaseUpdateBase
{
    public function run()
    {
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'themeOptions/updateSurvey',
                    'data' => '{"render": {"link": { "pjaxed": true, "data": {"sid": ["survey","sid"], "gsid":["survey","gsid"]}}}}'
                ),
                "name='theme_options'"
            );

    }
}