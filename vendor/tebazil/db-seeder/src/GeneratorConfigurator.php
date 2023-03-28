<?php
/**
 * Created by PhpStorm.
 * User: tebazil
 * Date: 09.09.15
 * Time: 19:46
 */

namespace tebazil\dbseeder;

/**
 * Class GeneratorConfigurator
 * @package tebazil\dbseeder
 */
class GeneratorConfigurator
{

    public $pk = Generator::PK;
    private $fakerConfigurator;

    public function __construct()
    {
        $this->fakerConfigurator = new FakerConfigurator();
    }

    public function pk() {
        return Generator::PK;
    }

    public function relation($table, $column)
    {
        return [Generator::RELATION, $table, $column];
    }

    /**
     * @return FakerConfigurator
     */
    public function getFakerConfigurator()
    {
        return $this->fakerConfigurator;
    }


}