            $oTransaction = $oDB->beginTransaction();
            $dir = new DirectoryIterator(APPPATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'plugins');
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    $plugin = $oDB->createCommand()
                        ->select('*')
                        ->from('{{plugins}}')
                        ->where("name = :name", [':name' => $fileinfo->getFilename()])
                        ->queryRow();

                    if (!empty($plugin)) {
                        if ($plugin['plugin_type'] !== 'core') {
                            $oDB->createCommand()->update(
                                '{{plugins}}',
                                ['plugin_type' => 'core'],
                                'name = :name',
                                [':name' => $plugin->name]
                            );
                        }
                    } else {
                        // Plugin in folder but not in database?
                    }
                }
            }

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 473), "stg_name='DBVersion'");
            $oTransaction->commit();
