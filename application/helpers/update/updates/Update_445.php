
            $table = '{{surveymenu_entries}}';
            $data_to_be_updated = [
                'data' => '{"render": {"isActive": false, "link": {"data": {"iSurveyID": ["survey","sid"]}}}}',
            ];
            $where = "name = 'activateSurvey'";
            $oDB->createCommand()->update(
                $table,
                $data_to_be_updated,
                $where
            );

            // Increase Database version
            $oDB->createCommand()->update(
                '{{settings_global}}',
                array('stg_value' => 445),
                "stg_name = 'DBVersion'"
            );

            $oTransaction->commit();
