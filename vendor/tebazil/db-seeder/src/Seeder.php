<?php
/**
 * Created by PhpStorm.
 * User: tebazil
 * Date: 10.08.15
 * Time: 21:14
 */

namespace tebazil\dbseeder;

use PDO;

class Seeder
{
    /**
     * @var Table[]
     */
    private $tables=[];
    private $filledTablesNames=[];
    protected $generatorConfigurator;
    protected $generator;
    protected $dbHelper;

    public function __construct(PDO $pdo) {
        $this->generator = new Generator();
        $this->dbHelper = new DbHelper($pdo);
        $this->generatorConfigurator = new GeneratorConfigurator();
    }

    public function table($name) {
        if(!isset($this->tables[$name])) {
            $this->tables[$name]=new Table($name, $this->generator, $this->dbHelper);
        }
        return new TableConfigurator($this->tables[$name]);
    }

    public function refill()
    {
        $this->checkCrossDependentTables();
        $tableNames = array_keys($this->tables);
        sort($tableNames);
        $foolProofCounter=0;
        $tableNamesIntersection=[];
        while($tableNamesIntersection!==$tableNames) {
            if($foolProofCounter++>500) {
                throw new \Exception("Something unexpected happened: some tables possibly cannot be filled");
            }
            foreach($this->tables as $tableName=>$table) {
                if(!$table->getIsFilled() && $table->canBeFilled($this->filledTablesNames)) {
                    $table->fill();
                    $this->generator->setColumns($tableName, $table->getColumns());
                    if(!in_array($tableName, $this->filledTablesNames)) { // because some tables are filled twice
                        $this->filledTablesNames[]=$tableName;
                    }
                }
            }
            $tableNamesIntersection = array_intersect($this->filledTablesNames, $tableNames);
            sort($tableNamesIntersection);
        }
    }

    private function checkCrossDependentTables() {
        $dependencyMap = [];
        foreach($this->tables as $tableName=>$table) {
            $dependencyMap[$tableName] = $table->getDependsOn();
        }
        foreach($dependencyMap as $tableName=>$tableDependencies) {
            foreach($tableDependencies as $dependencyTableName) {
                if(in_array($tableName, $dependencyMap[$dependencyTableName])) {
                    throw new \InvalidArgumentException("You cannot pass tables that are dependent on each other");
                }
            }
        }
    }

    /**
     * @return GeneratorConfigurator
     */
    public function getGeneratorConfigurator()
    {
        return $this->generatorConfigurator;
    }

}