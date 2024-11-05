<?php
/**
 * Created by PhpStorm.
 * User: tebazil
 * Date: 09.09.15
 * Time: 18:10
 */

namespace tebazil\dbseeder;


class TableConfigurator
{
    private $table;
    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function columns($columns)
    {
        $columns = $this->preprocess($columns);
        $this->table->setColumns($columns);
        return $this;
    }

    public function rowQuantity($rows=30)
    {
        $this->table->setRowQuantity($rows);
        return $this;
    }

    public function data(array $rawData, array $columnNames=[]) {
        $this->table->setRawData($rawData, $columnNames);
        return $this;
    }

    private function preprocess($columns) {
        foreach($columns as $key=>$value) {
            if(is_numeric($key)) {
                if(!is_scalar($value)) {
                    throw new \Exception("If the column is lazy configured, it's value should be scalar - either id, or foreign key, i.e. status_id");
                }
                $config = explode('_', $value);
                if($config[0]==='id') {
                    $newColumns[$value]=[Generator::PK];
                }
                elseif (sizeof($config) === 2 || $config[1] === 'id') {
                    $newColumns[$value] = [Generator::RELATION, $config[0], 'id'];
                }
                else {
                    throw new \Exception("Column ".$value." is badly lazy-configured");
                }
            }
            else {
                $newColumns[$key]=$value;
            }
        }
        return $newColumns;
    }


}