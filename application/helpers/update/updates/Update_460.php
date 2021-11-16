<?php

namespace LimeSurvey\Helpers\Update;

class Update_460 extends DatabaseUpdateBase
{
    public function up()
    {
            $installedPlugins = array_map(
                function ($v) {
                    return $v['name'];
                },
                $this->db->createCommand('SELECT name FROM {{plugins}}')->queryAll()
            );

            /**
             * @param string $name Name of plugin
             * @param int $active
             */
            $insertPlugin = function ($name, $active = 0) use ($installedPlugins) {
                if (!in_array($name, $installedPlugins)) {
                    $this->db->createCommand()->insert(
                        "{{plugins}}",
                        [
                            'name'               => $name,
                            'plugin_type'        => 'core',
                            'active'             => $active,
                            'version'            => '1.0.0',
                            'load_error'         => 0,
                            'load_error_message' => null
                        ]
                    );
                } else {
                    $this->db->createCommand()->update(
                        "{{plugins}}",
                        [
                            'plugin_type' => 'core',
                            'version'     => '1.0.0',
                        ],
                        $this->db->quoteColumnName('name') . " = " . dbQuoteAll($name)
                    );
                }
            };
            $insertPlugin('ExportSPSSsav', 1);
    }
}
