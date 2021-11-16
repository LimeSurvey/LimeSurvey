            $oTransaction = $oDB->beginTransaction();
            addColumn('{{surveys}}', 'googleanalyticsstyle', "string(1)");
            addColumn('{{surveys}}', 'googleanalyticsapikey', "string(25)");
            try {
                setTransactionBookmark();
                $oDB->createCommand()->renameColumn('{{surveys}}', 'showXquestions', 'showxquestions');
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 155), "stg_name='DBVersion'");
            $oTransaction->commit();
