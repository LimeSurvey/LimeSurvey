<?php

namespace LimeSurvey\Helpers\Update;

class Update_489 extends DatabaseUpdateBase
{
    /**
     * This table is needed to collect failed emails.
     */
    public function up()
    {
        $this->db->createCommand()->createTable(
            '{{failed_email}}',
            [
                'id' => "pk",
                'subject' => "string(200) NOT NULL",
                'recipient' => "string(200) NOT NULL",
                'content' => "text NOT NULL",
                'created' => "datetime NOT NULL",  //this one has always to be set to delete after x days ...
                'status' => "string(20) NULL DEFAULT 'SEND FAILED'",
                'updated' => "datetime NULL",
            ]
        );
    }

}
