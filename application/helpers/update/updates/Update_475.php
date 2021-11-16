            // Apply integrity fix before adding unique constraint.
            // List of label set ids which contain code duplicates.
            $lids = $oDB->createCommand(
                "SELECT {{labels}}.lid AS lid
                FROM {{labels}}
                GROUP BY {{labels}}.lid
                HAVING COUNT(DISTINCT({{labels}}.code)) < COUNT({{labels}}.id)"
            )->queryAll();
            foreach ($lids as $lid) {
                regenerateLabelCodes400($lid['lid'], $hasLanguageColumn = false);
            }
            $oDB->createCommand()->createIndex('{{idx5_labels}}', '{{labels}}', ['lid','code'], true);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 475), "stg_name='DBVersion'");
            $oTransaction->commit();
