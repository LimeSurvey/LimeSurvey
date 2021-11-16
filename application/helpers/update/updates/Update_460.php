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
                            'name'               => $name,
                            'plugin_type'        => 'core',
                            'active'             => $active,
                            'version'            => '1.0.0',
                            'load_error'         => 0,
                            'load_error_message' => null
                        ]
                    );
                } else {
                    $oDB->createCommand()->update(
                        "{{plugins}}",
                        [
                            'plugin_type' => 'core',
                            'version'     => '1.0.0',
                        ],
                        App()->db->quoteColumnName('name') . " = " . dbQuoteAll($name)
                    );
                }
            };
            $insertPlugin('ExportSPSSsav', 1);
