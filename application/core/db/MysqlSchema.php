<?php

class MysqlSchema extends CMysqlSchema
{
    public function __construct($conn)
    {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'int(11) NOT NULL AUTO_INCREMENT';
        $this->columnTypes['longbinary'] = 'longblob';
    }

    public function createTable($table, $columns, $options = null)
    {
        if (empty($options)) {
            $options = 'ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        }

        // Below copied from parent.
        $cols = array();
        foreach ($columns as $name => $type) {
            if (is_array($type) && $name == 'composite_pk') {
                // ...except this line.
                $cols[] = "\t".$this->getCompositePrimaryKey($type);
            } elseif (is_string($name)) {
                $cols[] = "\t".$this->quoteColumnName($name).' '.$this->getColumnType($type);
            } else {
                $cols[] = "\t".$type;
            }
        }
        $sql = "CREATE TABLE ".$this->quoteTableName($table)." (\n".implode(",\n", $cols)."\n)";
        return $options === null ? $sql : $sql.' '.$options;
    }

    /**
     * Get composite primary key definition.
     * @param array $columns
     * @return string
     */
    public function getCompositePrimaryKey(array $columns)
    {
        $columns = array_map(
            function($column)
            {
                return '`'.$column.'`';
            },
            $columns
        );
        return sprintf(
            'PRIMARY KEY (%s)',
            implode(', ', $columns)
        );
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
            if (preg_match('/^([a-zA-Z ]+)\((.+?)\)(.*)$/', $baseType, $baseMatches)) {
// Replace the default Yii param
                $sResult = preg_replace('/\(.+\)/', "(".$matches[2].")", parent::getColumnType($matches[1]." ".$matches[3]));
            } else {
// Get the base type and join
                $sResult = join(" ", array($baseType, "(".$matches[2].")", $matches[3]));
            }
        } else {
            $sResult = parent::getColumnType($type);
        }
        return $sResult;
    }
}
