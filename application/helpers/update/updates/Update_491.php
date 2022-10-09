<?php

namespace LimeSurvey\Helpers\Update;

class Update_491 extends DatabaseUpdateBase
{
    public function up()
    {
        // Upate 489 belongs with this update. Due to a faulty deployment, we start from scratch here with failed_emails table.
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropTable('{{failed_emails}}');
        } catch (\Exception $e) {
            // Ignore
            rollBackToTransactionBookmark();
        }
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropTable('{{failed_email}}');
        } catch (\Exception $e) {
            // Ignore
            rollBackToTransactionBookmark();
        }
        $update = new Update_489($this->db, $this->options);
        $update->up();  // up() instead of safeUp() to not nest database transactions

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
