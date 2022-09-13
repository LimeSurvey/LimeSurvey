<?php

namespace LimeSurvey\Helpers\Update;

class Update_491 extends DatabaseUpdateBase
{
    public function up()
    {
        $responseidColumn = $this->db->getSchema()->getTable('{{failed_emails}}')->getColumn('responseid');
        if ($responseidColumn === null) {
            $this->db->createCommand()->addColumn(
                '{{failed_emails}}',
                'responseid',
                "integer NOT NULL"
            );
        }
        $resendVarsColumn = $this->db->getSchema()->getTable('{{failed_emails}}')->getColumn('resend_vars');
        if ($resendVarsColumn === null) {
            $this->db->createCommand()->addColumn(
                '{{failed_emails}}',
                'resend_vars',
                "text NOT NULL"
            );
        }
    }
}
