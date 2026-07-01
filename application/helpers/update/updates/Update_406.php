<?php

namespace LimeSurvey\Helpers\Update;

use Exception;
use Token;

class Update_406 extends DatabaseUpdateBase
{
    public function up()
    {
        // surveys
        $this->db->createCommand()->addColumn('{{surveys}}', 'tokenencryptionoptions', "text");
        $this->db->createCommand()->update(
            '{{surveys}}',
            array(
                'tokenencryptionoptions' => json_encode(
                    Token::getDefaultEncryptionOptions()
                )
            )
        );
        // participants
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('{{idx1_participants}}', '{{participants}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        try {
            setTransactionBookmark();
            $this->db->createCommand()->dropIndex('{{idx2_participants}}', '{{participants}}');
        } catch (\Exception $e) {
            rollBackToTransactionBookmark();
        }
        \alterColumn('{{participants}}', 'firstname', "text");
        \alterColumn('{{participants}}', 'lastname', "text");
        $this->db->createCommand()->addColumn('{{participant_attribute_names}}', 'encrypted', "string(5) NOT NULL DEFAULT ''");
        $this->db->createCommand()->addColumn('{{participant_attribute_names}}', 'core_attribute', "string(5) NOT NULL DEFAULT ''");
        $aCoreAttributes = array('firstname', 'lastname', 'email');
        foreach ($aCoreAttributes as $attribute) {
            $this->db->createCommand()->insert(
                '{{participant_attribute_names}}',
                array(
                    'attribute_type' => 'TB',
                    'defaultname' => $attribute,
                    'visible' => 'TRUE',
                    'encrypted' => 'N',
                    'core_attribute' => 'Y'
                )
            );
        }
        $this->db->createCommand()->addColumn('{{questions}}', 'encrypted', "string(1) NULL DEFAULT 'N'");
    }
}
