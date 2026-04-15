<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_620 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()
            ->update(
                '{{boxes}}',
                [
                    'desc' => 'Label sets can be used as answer options or subquestions to speed up creation of similar questions.',
                ],
                "title = 'LimeStore' AND page = 'welcome'"
            );
    }
}
