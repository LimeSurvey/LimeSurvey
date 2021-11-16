<?php

namespace LimeSurvey\Helpers\Update;

class Update_419 extends DatabaseUpdateBase
{
    public function run()
    {
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

    }
}