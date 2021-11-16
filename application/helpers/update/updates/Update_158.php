<?php

namespace LimeSurvey\Helpers\Update;

class Update_158 extends DatabaseUpdateBase
{
    public function run()
    {
            LimeExpressionManager::UpgradeConditionsToRelevance();
    }
}