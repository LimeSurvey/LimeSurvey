<?php

class PgsqlSchema extends CPgsqlSchema
{

    public function __construct($conn)
    {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'serial';
        $this->columnTypes['longbinary'] = 'bytea';
        $this->columnTypes['decimal'] = 'numeric (10,0)'; // Same default than MySql (not used)
        $this->columnTypes['mediumtext'] = 'text';
        $this->columnTypes['longtext'] = 'text';
    }

    /**
     * Adds support for replacing default arguments.
     * @param string $type
     * @return string
     */
    public function getColumnType($type)
    {
        if (isset($this->columnTypes[$type])) {
            // Direct : get it
            $sResult = $this->columnTypes[$type];
        } elseif (preg_match('/^([a-zA-Z ]+)\((.+?)\)(.*)$/', $type, $matches)) {
            // With params : some test to do
            $baseType = parent::getColumnType($matches[1]);
            if (preg_match('/^([a-zA-Z ]+)\((.+?)\)(.*)$/', (string) $baseType, $baseMatches)) {
                // Replace the default Yii param
                $sResult = preg_replace('/\(.+\)/', "(" . $matches[2] . ")", (string) parent::getColumnType($matches[1] . " " . $matches[3]));
            } else {
                // Get the base type and join
                $sResult = join(" ", array($baseType, "(" . $matches[2] . ")", $matches[3]));
            }
        } else {
            $sResult = parent::getColumnType($type);
        }
        return $sResult;
    }

    public function createTable($table, $columns, $options = null)
    {
        // Below copied from parent.
        $cols = array();
        foreach ($columns as $name => $type) {
            if (is_array($type) && $name == 'composite_pk') {
                // ...except this line.
                $cols[] = "\t" . $this->getCompositePrimaryKey($table, $type);
            } elseif (is_string($name)) {
                $cols[] = "\t" . $this->quoteColumnName($name) . ' ' . $this->getColumnType($type);
            } else {
                $cols[] = "\t" . $type;
            }
        }
        $sql = "CREATE TABLE " . $this->quoteTableName($table) . " (\n" . implode(",\n", $cols) . "\n)";
        return $options === null ? $sql : $sql . ' ' . $options;
    }

    /**
     * Get composite primary key definition.
     * CONSTRAINT prefix_assessments_pkey PRIMARY KEY (id,language)
     * @param string $table
     * @param array $columns
     * @return string
     */
    public function getCompositePrimaryKey($table, array $columns)
    {
        return sprintf(
            'CONSTRAINT %s_composite_pkey PRIMARY KEY (%s)',
            $table,
            implode(', ', $columns)
        );
    }
}
