<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_800 extends DatabaseUpdateBase
{
    /**
     * Global setting names to rename, old name => new name.
     *
     * @var array<string, string>
     */
    private const RENAMED_SETTINGS = [
        'blacklistallsurveys' => 'blocklistallsurveys',
        'blacklistnewsurveys' => 'blocklistnewsurveys',
        'hideblacklisted' => 'hideblocklisted',
        'deleteblacklisted' => 'deleteblocklisted',
        'allowunblacklist' => 'allowunblocklist',
        'loginIpWhitelist' => 'loginIpAllowlist',
        'tokenIpWhitelist' => 'tokenIpAllowlist',
    ];

    /**
     * Replace the blacklist/whitelist wording with blocklist/allowlist:
     * rename the 'blacklisted' column of the central participants table and of all
     * active and archived survey participant tables to 'blocklisted', and rename the
     * related global settings.
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $tables = array_merge(
            ['{{participants}}'],
            dbGetTablesLike('tokens%'),
            dbGetTablesLike('old_tokens%')
        );
        foreach ($tables as $table) {
            $this->renameBlocklistedColumn($table);
        }

        foreach (self::RENAMED_SETTINGS as $oldName => $newName) {
            $this->renameGlobalSetting($oldName, $newName);
        }
    }

    /**
     * Rename the 'blacklisted' column of the given table to 'blocklisted', if the table has it.
     *
     * @param string $table Table name, either with {{ }} placeholder or with the real prefix
     * @return void
     * @throws CException
     */
    private function renameBlocklistedColumn(string $table): void
    {
        $tableSchema = $this->db->getSchema()->getTable($table, true);
        if ($tableSchema === null || $tableSchema->getColumn('blacklisted') === null) {
            return;
        }
        $this->db->createCommand()->renameColumn($table, 'blacklisted', 'blocklisted');
    }

    /**
     * Rename a global setting. If a setting with the new name already exists, the old one is dropped.
     *
     * @param string $oldName Current setting name
     * @param string $newName New setting name
     * @return void
     * @throws CException
     */
    private function renameGlobalSetting(string $oldName, string $newName): void
    {
        $newExists = (int) $this->db->createCommand()
            ->select('COUNT(*)')
            ->from('{{settings_global}}')
            ->where('stg_name = :name', [':name' => $newName])
            ->queryScalar();
        if ($newExists > 0) {
            $this->db->createCommand()->delete('{{settings_global}}', 'stg_name = :name', [':name' => $oldName]);
            return;
        }
        $this->db->createCommand()->update(
            '{{settings_global}}',
            ['stg_name' => $newName],
            'stg_name = :name',
            [':name' => $oldName]
        );
    }
}
