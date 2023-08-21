<?php

namespace LimeSurvey\Helpers\Update;

class Update_484 extends DatabaseUpdateBase
{
    public function up()
    {
        $comfortUpdatePlugins = $this->db->createCommand()
            ->select('*')
            ->from('{{plugins}}')
            ->where("name = :name", [':name' => 'ComfortUpdateChecker'])
            ->queryAll();

        // is it two or more
        if (count($comfortUpdatePlugins) > 1) {
            // if yes uninstall all but the first
            foreach ($comfortUpdatePlugins as $index => $cUPlugin) {
                if ($index > 0) {
                    $this->db->createCommand()->delete('{{plugins}}', 'id=:id', [':id' => $cUPlugin['id']]);
                }
            }
        } elseif (count($comfortUpdatePlugins) == 0) {
            // If no ComfortUpdate plugin entry exists, add a proper one
            $newCUPlugin = [
                'name' => 'ComfortUpdateChecker',
                'plugin_type' => 'core',
                'active' => 1,
                'version' => '1.0.0'
            ];
            $this->db->createCommand()->insert('{{plugins}}', $newCUPlugin);
        }
        // remaining one
        if (count($comfortUpdatePlugins) > 0) {
            // If disabled, enable it and if not marked as core, mark it as core.
            if ($comfortUpdatePlugins[0]['active'] == 0 || $comfortUpdatePlugins[0]['plugin_type'] !== 'core') {
                $this->db->createCommand()->update(
                    "{{plugins}}",
                    ['plugin_type' => 'core', 'active' => 1],
                    'id=:id',
                    [':id' => $comfortUpdatePlugins[0]['id']]
                );
            }
        }
    }
}
