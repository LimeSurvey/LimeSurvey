<?php

namespace LimeSurvey\Helpers\Update;

class Update_153 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->createTable(
                '{{expression_errors}}',
                array(
                    'id' => 'pk',
                    'errortime' => 'string(50)',
                    'sid' => 'integer',
                    'gid' => 'integer',
                    'qid' => 'integer',
                    'gseq' => 'integer',
                    'qseq' => 'integer',
                    'type' => 'string(50)',
                    'eqn' => 'text',
                    'prettyprint' => 'text'
                )
            );
    }
}
