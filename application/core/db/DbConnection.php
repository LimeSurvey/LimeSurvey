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

    /**
     * Creates the PDO instance, using LimeSurveyMssqlPdoAdapter instead of Yii's stock
     * CMssqlPdoAdapter for the 'mssql'/'dblib' drivers so that PDO::inTransaction()
     * correctly reflects transactions opened via BEGIN TRANSACTION (see bug #19016:
     * without this, CDbTransaction::commit()/rollback() silently skip the real
     * COMMIT/ROLLBACK TRANSACTION because the stock adapter never updates PDO's
     * transaction state, leaving the transaction open and later rolled back by the
     * server when the connection closes).
     *
     * @return PDO
     * @throws CDbException
     */
    protected function createPdoInstance()
    {
        $driver = $this->getDriverName();
        if ($driver !== 'mssql' && $driver !== 'dblib') {
            return parent::createPdoInstance();
        }

        $pdoClass = 'LimeSurveyMssqlPdoAdapter';
        if (!class_exists($pdoClass)) {
            throw new CDbException(
                Yii::t(
                    'yii',
                    'CDbConnection is unable to find PDO class "{className}". Make sure PDO is installed correctly.',
                    array('{className}' => $pdoClass)
                )
            );
        }

        @$instance = new $pdoClass($this->connectionString, $this->username, $this->password, $this->getAttributes());
        if (!$instance) {
            throw new CDbException(Yii::t('yii', 'CDbConnection failed to open the DB connection.'));
        }

        return $instance;
    }

    protected function initConnection($pdo)
    {
        parent::initConnection($pdo);
        $driver = strtolower((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        if (in_array($driver, array('mysql', 'mysqli'))) {
            $pdo->exec("SET collation_connection='utf8mb4_unicode_ci'");
            $pdo->exec("SET SESSION time_zone = '+00:00'");
            if (Yii::app()->getConfig('debug') > 1) {
                $pdo->exec("SET SESSION SQL_MODE='STRICT_ALL_TABLES,IGNORE_SPACE,ONLY_FULL_GROUP_BY'");
            }
        } elseif ($driver === 'pgsql') {
            $pdo->exec("SET timezone = 'UTC'");
        } elseif (in_array($driver, array('mssql', 'dblib', 'sqlsrv'))) {
            $pdo->exec("SET DATEFIRST 7");
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
            return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
        }
    }
}
