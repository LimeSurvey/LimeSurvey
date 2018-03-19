<?php
/**
* @property boolean $active Whether the DB connection is established. 
*/
class DbConnection extends \CDbConnection
{
    public function __construct($dsn = '', $username = '', $password = '')
    {
        parent::__construct($dsn, $username, $password);
        $this->driverMap = array_merge($this->driverMap, array(
            'mysql' => 'MysqlSchema',
            'mysqli' => 'MysqlSchema',
            'mssql' => 'MssqlSchema',
            'dblib' => 'MssqlSchema',
            'sqlsrv' => 'MssqlSchema',
            'pgsql' => 'PgsqlSchema'
        ));
    }

    protected function initConnection($pdo)
    {
        parent::initConnection($pdo);
        $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        if (in_array($driver, array('mysql', 'mysqli'))) {
            $pdo->exec("SET collation_connection='utf8mb4_unicode_ci'");
            if (Yii::app()->getConfig('debug') > 1) {
                $pdo->exec("SET SESSION SQL_MODE='STRICT_ALL_TABLES,ANSI'");
            } 
        }
    }
        
    /**
     * Quotes a string value for use in a query.
     * @param string $str string to be quoted
     * @param integer $quoteParam Parameter for PDO::quote function.
     * @return string the properly quoted string
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValueExtended($str, $quoteParam)
    {
        if (is_int($str) || is_float($str)) {
                    return $str;
        }

        $this->setActive(true);
        if (($value = $this->getPdoInstance()->quote($str, $quoteParam)) !== false) {
                    return $value;
        } else {
            // the driver doesn't support quote (e.g. oci)
            return "'".addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032")."'";
        }
    }
}
