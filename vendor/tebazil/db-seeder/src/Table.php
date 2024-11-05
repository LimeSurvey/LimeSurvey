<?php
namespace tebazil\dbseeder;

use Helper;

class Table {
    const DEFAULT_ROW_QUANTITY=30;
    private $generator;
    private $dbHelper;
    private $name;
    private $columns;
    private $rawData;
    private $rows;
    private $isFilled=false;
    private $isPartiallyFilled=false;
    private $dependsOn=[];
    private $selfDependentColumns=[];
    private $columnConfig=[];
    private $rowQuantity;

    public function __construct($name, Generator $generator, $dbHelper) {
        $this->name = $name;
        $this->generator = $generator;
        /**
         * @var $dbHelper DbHelper
         */
        $this->dbHelper = $dbHelper;
        $this->rowQuantity = self::DEFAULT_ROW_QUANTITY;
    }



    public function setColumns($columns)
    {
        //initialize columns
        $columnNames = array_keys($columns);
            foreach($columnNames as $columnName) {
                $this->columns[$columnName]=[];
            }
        //column config
        $this->columnConfig = $columns;
        $this->calcDependsOn();
        $this->calcSelfDependentColumns();
        return $this;
    }

    public function setRowQuantity($rows=30) {
        if(is_numeric($rows)) {
            $this->rowQuantity = $rows;
        }
        else
            throw new \Exception('$rows parameter should be numeric');
        return $this;
    }

    public function setRawData(array $rawData, array $columnNames=[])
    {
        if(!$rawData) {
            throw new \InvalidArgumentException('$rawData cannot be empty array');
        }
        if(!is_array($firstRow = reset($rawData))) {
            throw new \InvalidArgumentException('$rawData should be an array of arrays (2d array)');
        }
        if(is_numeric(key($firstRow)) && !$columnNames) {
            throw new \InvalidArgumentException('Either provide $rawData line arrays with corresponding column name keys, or provide column names in $columnNames');
        }

        $this->rawData = $rawData;
        $columnNames = $columnNames ?: array_keys(reset($this->rawData));
        $this->columnConfig = []; //just in case
        foreach($columnNames as $columnName) {
            if($columnName) {
                $this->columns[$columnName] = []; //we skip false columns and empty columns
            }
            $this->columnConfig[]=$columnName;

        }
        return $this;
    }

    public function fill($writeDatabase = true) {
        is_null($this->rawData) ? $this->fillFromGenerators($this->columnConfig): $this->fillFromRawData($this->columnConfig, $this->rawData);

        if($this->selfDependentColumns) {
            if($this->isPartiallyFilled) {
                $this->isFilled = true; //second run
            }
            else {
                $this->isPartiallyFilled = true; //first run
            }
        }
        else {
            $this->isFilled = true; //no self-dependent columns
        }

        if($this->isFilled && $writeDatabase) {
            $this->truncate();
            $this->insertData();
        }
    }

    public function getIsFilled() {
        return $this->isFilled;
    }

    public function canBeFilled($filledTableNames) {
        $intersection = array_intersect($filledTableNames, $this->dependsOn);
        sort($intersection);
        return $intersection===$this->dependsOn;
    }

    private function fillFromRawData($columnConfig, $data) {
        //todo size mismatch detect ?

        $sizeofColumns = sizeof($columnConfig);
        $data = array_values($data);
        for($rowNo=0; $rowNo<$this->rowQuantity;$rowNo++) {
                $dataKey = ($rowNo < sizeof($data)) ? $rowNo : ($rowNo % sizeof($data));
                $rowData = array_values($data[$dataKey]);
                for ($i = 0; $i < $sizeofColumns; $i++) {
                    if(!$columnConfig[$i]) { //skipped column
                        continue;
                    }
                    $this->rows[$rowNo][$columnConfig[$i]] = $rowData[$i];
                    $this->columns[$columnConfig[$i]][$rowNo] = $rowData[$i];
                }
        }

    }

    private function fillFromGenerators($columnConfig) {
        $this->generator->reset();
        for($rowNo=0; $rowNo<$this->rowQuantity;$rowNo++) {
            foreach ($columnConfig as $column => $config) {
                //first and second run separation
                if($this->selfDependentColumns) {
                    $columnIsSelfDependent = in_array($column, $this->selfDependentColumns);
                    if(!$this->isPartiallyFilled) {
                        if($columnIsSelfDependent) {
                            continue;
                        }
                    }
                    else {
                        if(!$columnIsSelfDependent) {
                            continue;
                        }
                    }
                }
                $value = $this->generator->getValue($config);
                $this->rows[$rowNo][$column] = $value;
                $this->columns[$column][$rowNo] = $value;
            }
        }
    }

    private function calcDependsOn() {
        if($this->rawData) {
            return false;
        }
        else {
            foreach ($this->columnConfig as $name => $config) {
                if (!is_callable($config)) {
                    if (is_array($config) && ($config[0] === Generator::RELATION) && ($this->name!==$config[1])) {
                        $this->dependsOn[] = $config[1];
                    }
                }
            }
            sort($this->dependsOn);
        }
    }

    private function calcSelfDependentColumns() {
        if($this->rawData) {
            return false;
        }
        else {
            foreach ($this->columnConfig as $name => $config) {
                if (!is_callable($config)) {
                    if (is_array($config) && ($config[0] === Generator::RELATION) && ($config[1]===$this->name)) {
                            $this->selfDependentColumns[]=$name;
                    }
                }
            }
        }
    }

    private function truncate()
    {
        $this->dbHelper->truncateTable($this->name);

    }

    private function insertData()
    {
        foreach($this->rows as $row) {
            $this->dbHelper->insert($this->name, $row);
        }
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }






}