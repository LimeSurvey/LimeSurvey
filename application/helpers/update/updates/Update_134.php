            // Add new tokens setting
            addColumn('{{surveys}}', 'usetokens', "string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}', 'attributedescriptions', 'text');
            dropColumn('{{surveys}}', 'attribute1');
            dropColumn('{{surveys}}', 'attribute2');
            upgradeTokenTables134();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 134), "stg_name='DBVersion'");
            $oTransaction->commit();
