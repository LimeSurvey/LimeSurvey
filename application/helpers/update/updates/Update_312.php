<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_312 extends DatabaseUpdateBase
{
    public function up()
    {
        // Already added in beta 2 but with wrong type
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropColumn('{{template_configuration}}', 'packages_ltr');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropColumn('{{template_configuration}}', 'packages_rtl');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }

        addColumn('{{template_configuration}}', 'packages_ltr', "text");
        addColumn('{{template_configuration}}', 'packages_rtl', "text");
    }
}
