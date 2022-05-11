<?php

namespace LimeSurvey\Helpers\Update;

class Update_174 extends DatabaseUpdateBase
{
    public function up()
    {
            \alterColumn('{{participants}}', 'email', "string(254)");
            \alterColumn('{{saved_control}}', 'email', "string(254)");
            \alterColumn('{{surveys}}', 'adminemail', "string(254)");
            \alterColumn('{{surveys}}', 'bounce_email', "string(254)");
        switch (\Yii::app()->db->driverName) {
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                dropUniqueKeyMSSQL('email', '{{users}}');
        }
            \alterColumn('{{users}}', 'email', "string(254)");
    }
}
