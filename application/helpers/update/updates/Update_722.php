<?php

namespace LimeSurvey\Helpers\Update;

use CDbColumnSchema;

class Update_722 extends DatabaseUpdateBase
{
    private const COLUMN = 'welcome_image';

    /**
     * Makes sure the surveys table has the welcome_image column, as a nullable long text column
     *
     * 7.12.0 shipped this column in DB version 718, which installations from 7.8.0 to 7.11.0 had already passed as an
     * empty update, so they never got it. Some administrators then added it by hand, possibly with another name case,
     * a shorter type or NOT NULL: Such a column is converted, its values are kept.
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        $table = $this->db->tablePrefix . 'surveys';
        $column = $this->findColumn($table);
        $isMysql = in_array($this->db->driverName, ['mysql', 'mysqli'], true);
        if ($column === null) {
            addColumn($table, self::COLUMN, $isMysql ? 'mediumtext NULL' : 'text NULL');
            return;
        }
        if ($column->name !== self::COLUMN) {
            $this->db->createCommand()->renameColumn($table, $column->name, self::COLUMN);
        }
        if (!$this->hasExpectedType($column)) {
            alterColumn($table, self::COLUMN, $isMysql ? 'mediumtext' : 'text');
        }
        $this->db->schema->getTable($table, true);
    }

    /**
     * Returns the welcome_image column of the table, also when it was added by hand with another name case
     *
     * @param string $table The table name with prefix
     * @return CDbColumnSchema|null
     */
    private function findColumn(string $table): ?CDbColumnSchema
    {
        foreach ($this->db->schema->getTable($table, true)->columns as $column) {
            if (strcasecmp($column->name, self::COLUMN) === 0) {
                return $column;
            }
        }
        return null;
    }

    /**
     * Checks whether the column is nullable and can hold at least as much text as the column of a fresh installation
     *
     * @param CDbColumnSchema $column
     * @return bool
     */
    private function hasExpectedType(CDbColumnSchema $column): bool
    {
        if (!$column->allowNull) {
            return false;
        }
        $dbType = strtolower((string) $column->dbType);
        switch ($this->db->driverName) {
            case 'mysql':
            case 'mysqli':
                return in_array($dbType, ['mediumtext', 'longtext'], true);
            case 'pgsql':
                return $dbType === 'text';
            default:
                // MSSQL: nvarchar(max) and varchar(max) have the size -1
                return in_array($dbType, ['ntext', 'text'], true)
                    || (in_array($dbType, ['nvarchar', 'varchar'], true) && (int) $column->size === -1);
        }
    }
}
