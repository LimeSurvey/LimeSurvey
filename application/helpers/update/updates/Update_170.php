            // renamed advanced attributes fields dropdown_dates_year_min/max
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('attribute' => 'date_min'),
                "attribute='dropdown_dates_year_min'"
            );
            $oDB->createCommand()->update(
                '{{question_attributes}}',
                array('attribute' => 'date_max'),
                "attribute='dropdown_dates_year_max'"
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 170), "stg_name='DBVersion'");
            $oTransaction->commit();
