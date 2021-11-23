<?php

namespace LimeSurvey\Helpers\Update;

use DirectoryIterator;

class Update_473 extends DatabaseUpdateBase
{
    public function up()
    {
        $dir = new DirectoryIterator(APPPATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'plugins');
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $plugin = $this->db->createCommand()
                    ->select('*')
                    ->from('{{plugins}}')
                    ->where("name = :name", [':name' => $fileinfo->getFilename()])
                    ->queryRow();

                if (!empty($plugin)) {
                    if ($plugin['plugin_type'] !== 'core') {
                        $this->db->createCommand()->update(
                            '{{plugins}}',
                            ['plugin_type' => 'core'],
                            'name = :name',
                            [':name' => $plugin['name']]
                        );
                    }
                } else {
                    // Plugin in folder but not in database?
                }
            }
        }
    }
}
