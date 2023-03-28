<?php
/**
 * Created by PhpStorm.
 * User: tebazil
 * Date: 10.08.15
 * Time: 22:27
 */

namespace tebazil\dbseeder;


class Generator
{
    const PK = 'pk';
    const FAKER = 'faker';
    const RELATION = 'relation';
    private $faker;
    private $reset = false;
    private $pkValue = 1;
    private $tables;

    public function __construct()
    {
        $this->faker = $this->getNewFakerInstance();
    }

    public function getValue($config)
    {
        if(!is_array($config)) {
            $config = [$config];
        }

        $value = null;
        switch ($config[0]) {
            case self::PK:
                $value = $this->pk();
                break;
            case self::FAKER:
                $faker = $this->faker;
                if(isset($config[3])) { //options

                    if(isset($config[3][FakerConfigurator::UNIQUE]) && is_array($config[3][FakerConfigurator::UNIQUE])) {
                        $faker = call_user_func_array([$faker, 'unique'], $config[3][FakerConfigurator::UNIQUE]);
                    }
                    if(isset($config[3][FakerConfigurator::OPTIONAL]) && is_array($config[3][FakerConfigurator::OPTIONAL])) {
                        $faker = call_user_func_array([$faker, 'optional'], $config[3][FakerConfigurator::OPTIONAL]);
                    }
                    if(isset($config[3][FakerConfigurator::VALID]) && is_array($config[3][FakerConfigurator::VALID])) {
                        $faker = call_user_func_array([$faker, 'valid'], $config[3][FakerConfigurator::VALID]);
                    }
                }
                if (isset($config[2])) {
                    $value = $faker->format($config[1], $config[2]);
                } else {
                    $value = $faker->format($config[1]);
                }
                break;
            case self::RELATION:
                if(!$this->isColumnSet($config[1], $config[2])) {
                    throw new \InvalidArgumentException("Table data for table $config[1] column $config[2] is not found in class instance. Probably this is a bug.");
                }
                $value = $this->getRandomColumnValue($config[1], $config[2]);
                break;
            default:
                if (is_callable($config[0])) {
                    return call_user_func($config[0]);
                }
                else return $config[0];
                break;
        }
        return $value;
    }

    public function reset()
    {
        $this->reset = true;
    }

    private function pk()
    {
        if ($this->reset) {
            $this->pkValue = 1;
            $this->reset = false;
        }
        return $this->pkValue++;

    }

    private function getNewFakerInstance()
    {
        return \Faker\Factory::create();
    }

    private function isColumnSet($table, $column) {
        return isset($this->tables[$table]) && isset($this->tables[$table][$column]);
    }

    public function setColumns($table, $columns) {
        $this->tables[$table] = $columns;
    }

    public function getRandomColumnValue($table, $column) {
        if(isset($this->tables[$table][$column])) {
            return $this->tables[$table][$column][array_rand($this->tables[$table][$column])];
        }
        else throw new \InvalidArgumentException("Table $table , column $column is not filled");
    }


}
