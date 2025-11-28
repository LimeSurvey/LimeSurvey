<?php

namespace LimeSurvey\Helpers\Update;

class Update_639 extends DatabaseUpdateBase
{
    public function up()
    {
        //On cloud we had a change script for sharing features, but it was cloud-only and we need an empty change script here to be in sync with that
    }
}
