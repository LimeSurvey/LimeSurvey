<?php

namespace LimeSurvey\Helpers\Update;

class Update_424 extends DatabaseUpdateBase
{
    public function run()
    {
            $installedPlugins = array_map(
                function ($v) {
                    return $v['name'];
                },
                $oDB->createCommand('SELECT name FROM {{plugins}}')->queryAll()
            );
            /**
             * @param string $name Name of plugin
             * @param int $active
             */
            $insertPlugin = function ($name, $active = 0) use ($installedPlugins, $oDB) {
                if (!in_array($name, $installedPlugins)) {
                    $oDB->createCommand()->insert(
                        "{{plugins}}",
                        [
                            'name' => $name,
                            'plugin_type' => 'core',
                            'active' => $active,
                            'version' => '1.0.0',
                            'load_error' => 0,
                            'load_error_message' => null
                        ]
                    );
                } else {
                    $oDB->createCommand()->update(
                        "{{plugins}}",
                        [
                            'plugin_type' => 'core',
                            'version' => '1.0.0',
                        ],
                        App()->db->quoteColumnName('name') . " = " . dbQuoteAll($name)
                    );
                }
            };
            $insertPlugin('AuthLDAP');
            $insertPlugin('Authdb');
            $insertPlugin('ComfortUpdateChecker');
            $insertPlugin('AuditLog');
            $insertPlugin('Authwebserver');
            $insertPlugin('ExportR', 1);
            $insertPlugin('ExportSTATAxml', 1);
            $insertPlugin('oldUrlCompat');
            $insertPlugin('expressionQuestionHelp');
            $insertPlugin('expressionQuestionForAll');
            $insertPlugin('expressionFixedDbVar');
            $insertPlugin('customToken');
            $insertPlugin('mailSenderToFrom');
    }
}