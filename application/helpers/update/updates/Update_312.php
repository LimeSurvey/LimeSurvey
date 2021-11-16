            $oTransaction = $oDB->beginTransaction();
            // Already added in beta 2 but with wrong type
            try {
                setTransactionBookmark();
                $oDB->createCommand()->dropColumn('{{template_configuration}}', 'packages_ltr');
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            try {
                setTransactionBookmark();
                $oDB->createCommand()->dropColumn('{{template_configuration}}', 'packages_rtl');
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }

            addColumn('{{template_configuration}}', 'packages_ltr', "text");
            addColumn('{{template_configuration}}', 'packages_rtl', "text");
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 312), "stg_name='DBVersion'");
            $oTransaction->commit();
