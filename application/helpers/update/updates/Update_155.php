            addColumn('{{surveys}}', 'googleanalyticsstyle', "string(1)");
            addColumn('{{surveys}}', 'googleanalyticsapikey', "string(25)");
            try {
                setTransactionBookmark();
                $oDB->createCommand()->renameColumn('{{surveys}}', 'showXquestions', 'showxquestions');
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
