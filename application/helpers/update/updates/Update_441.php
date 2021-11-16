            // Convert old html editor modes if present in global settings
            $oDB->createCommand()->update(
                '{{settings_global}}',
                array(
                    'stg_value' => 'inline',
                ),
                "stg_name='defaulthtmleditormode' AND stg_value='wysiwyg'"
            );
            $oDB->createCommand()->update(
                '{{settings_global}}',
                array(
                    'stg_value' => 'none',
                ),
                "stg_name='defaulthtmleditormode' AND stg_value='source'"
            );
            // Convert old html editor modes if present in profile settings
            $oDB->createCommand()->update(
                '{{users}}',
                array(
                    'htmleditormode' => 'inline',
                ),
                "htmleditormode='wysiwyg'"
            );
            $oDB->createCommand()->update(
                '{{users}}',
                array(
                    'htmleditormode' => 'none',
                ),
                "htmleditormode='source'"
            );
