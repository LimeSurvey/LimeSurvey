<?php

class MysqlSchema extends CMysqlSchema
{
    const ENGINE_INNODB = 'InnoDB';
    const ENGINE_MYISAM = 'MyISAM';
    use SmartColumnTypeTrait;

    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'int(11) NOT NULL AUTO_INCREMENT';
        
        $this->columnTypes['longbinary'] = 'longblob';
    }

    public function createTable($table, $columns, $options = null) {
        $result = parent::createTable($table, $columns, $options);
        $result .= ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        return $result;
    }
    
    public function getDatabases()
    {
        return $this->dbConnection->createCommand('SHOW DATABASES')->queryColumn(['Database']);
    }
    
    public function createDatabase($name)
    {
        $this->dbConnection->createCommand("CREATE DATABASE `$name` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci")->execute();
        return true;
    }

    public function dropDatabase($name)
    {
        $this->dbConnection->createCommand("DROP DATABASE `$name`")->execute();
        return true;
    }

    public function alterEngine($table, $engine) {
        $sql = "ALTER TABLE `$table` ENGINE=$engine";
        try {
            $this->dbConnection->createCommand($sql)->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function tableExists($name)
    {
        $tables = $this->dbConnection->createCommand('SHOW TABLES LIKE :name')->queryAll(false, [':name' => $name]);
        return count($tables) === 1;
    }

  }