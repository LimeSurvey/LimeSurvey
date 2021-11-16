            $oTransaction = $oDB->beginTransaction();
            // Delete duplicate template configurations
            $deleteQuery = "DELETE FROM {{template_configuration}}
                WHERE id NOT IN (
                    SELECT id FROM (
                        SELECT MIN(id) as id
                            FROM {{template_configuration}} t 
                            GROUP BY t.template_name, t.sid, t.gsid, t.uid
                    ) x
                )";
            $oDB->createCommand($deleteQuery)->execute();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 444), "stg_name='DBVersion'");
            $oTransaction->commit();
