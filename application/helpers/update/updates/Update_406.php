            // surveys
            $oDB->createCommand()->addColumn('{{surveys}}', 'tokenencryptionoptions', "text");
            $oDB->createCommand()->update(
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
                $oDB->createCommand()->dropIndex('{{idx1_participants}}', '{{participants}}');
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            try {
                setTransactionBookmark();
                $oDB->createCommand()->dropIndex('{{idx2_participants}}', '{{participants}}');
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            alterColumn('{{participants}}', 'firstname', "text");
            alterColumn('{{participants}}', 'lastname', "text");
            $oDB->createCommand()->addColumn('{{participant_attribute_names}}', 'encrypted', "string(5) NOT NULL DEFAULT ''");
            $oDB->createCommand()->addColumn('{{participant_attribute_names}}', 'core_attribute', "string(5) NOT NULL DEFAULT ''");
            $aCoreAttributes = array('firstname', 'lastname', 'email');
            foreach ($aCoreAttributes as $attribute) {
                $oDB->createCommand()->insert(
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
            $oDB->createCommand()->addColumn('{{questions}}', 'encrypted', "string(1) NULL default 'N'");

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 406), "stg_name='DBVersion'");
            $oTransaction->commit();
