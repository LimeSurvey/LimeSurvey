            $oDB->createCommand(
                "
                UPDATE
                    {{boxes}}
                SET ico = CASE
                    WHEN ico IN ('add', 'list', 'settings', 'shield', 'templates', 'label') THEN CONCAT('icon-', ico)
                    ELSE ico
                END
                "
            )->execute();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 405), "stg_name='DBVersion'");
            $oTransaction->commit();
