            $oDB->createCommand()->createTable(
                "{{permissiontemplates}}",
                [
                    'ptid' => "pk",
                    'name' => "string(127) NOT NULL",
                    'description' => "text NULL",
                    'renewed_last' => "datetime NULL",
                    'created_at' => "datetime NOT NULL",
                    'created_by' => "int NOT NULL"
                ]
            );

            $oDB->createCommand()->createIndex('{{idx1_name}}', '{{permissiontemplates}}', 'name', true);

            $oDB->createCommand()->createTable(
                '{{user_in_permissionrole}}',
                array(
                    'ptid' => "integer NOT NULL",
                    'uid' => "integer NOT NULL",
                ),
                $options
            );

            $oDB->createCommand()->addPrimaryKey(
                '{{user_in_permissionrole_pk}}',
                '{{user_in_permissionrole}}',
                ['ptid', 'uid']
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 419), "stg_name='DBVersion'");
            $oTransaction->commit();
