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
