<?php
/**
 * Created by PhpStorm.
 * User: tebazil
 * Date: 16.08.15
 * Time: 22:34
 */

namespace tebazil\dbseeder;


use PDO;

class DbHelper
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function insert($table, $columns)
    {
        $columnString = implode(', ',array_keys($columns));
        $placeholderValues = [];
        foreach($columns as $columnName => $value) {
            $placeholderValues[':'.$columnName]=$value;

        }
        $placeholderString = implode(', ',array_keys($placeholderValues));
        $sql = 'INSERT INTO '.$table.'('.$columnString.') VALUES ('.$placeholderString.')';
        echo '>>> Executing "'.$sql.'" with params: '.$this->getParamsForEcho($placeholderValues).PHP_EOL;
        $stmt = $this->pdo->prepare($sql);
        if(!$stmt instanceof \PDOStatement) {
            throw new \InvalidArgumentException(print_r($this->pdo->errorInfo()));
        }
        return $stmt->execute($placeholderValues);
    }

    public function truncateTable($table)
    {
        $sql = (strpos('sqlite',$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME))!==false ? "DELETE FROM ":"TRUNCATE TABLE ") . $table;
        echo 'Executing "'.$sql.'"'.PHP_EOL;
        $this->pdo->exec($sql);
    }

    private function getParamsForEcho($params) {
        $ret = '(';
        $pairs = [];
        foreach($params as $placeholder=>$value) {
            $pairs[]=$placeholder.' => '.$value;
        }

        $ret.=implode(', ',$pairs).')';
        return $ret;
    }

}