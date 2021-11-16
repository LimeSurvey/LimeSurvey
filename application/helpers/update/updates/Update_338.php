            $rowToRemove = $oDB->createCommand()->select("position, id")->from("{{boxes}}")->where(
                'ico=:ico',
                [':ico' => 'templates']
            )->queryRow();
            $position = 6;
            if ($rowToRemove !== false) {
                $oDB->createCommand()->delete("{{boxes}}", 'id=:id', [':id' => $rowToRemove['id']]);
                $position = $rowToRemove['position'];
            }
            $oDB->createCommand()->insert(
                "{{boxes}}",
                [
                    'position' => $position,
                    'url' => 'admin/themeoptions',
                    'title' => 'Themes',
                    'ico' => 'templates',
                    'desc' => 'Themes',
                    'page' => 'welcome',
                    'usergroup' => '-2'
                ]
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 338), "stg_name='DBVersion'");
            $oTransaction->commit();
