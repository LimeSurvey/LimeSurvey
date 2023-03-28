<?php
/**
 * Created by PhpStorm.
 * User: tebazil
 * Date: 16.08.15
 * Time: 23:26
 */

namespace tebazil\yii1seeder;
use tebazil\dbseeder\Generator;
use tebazil\dbseeder\GeneratorConfigurator;

class Seeder extends \tebazil\dbseeder\Seeder
{
    /**
     * @var \CDbConnection
     */
    private $db;
    public function __construct($db = 'db')
    {
        $this->generator = new Generator();
        $this->dbHelper = new Migration();
        if(!is_null($db)) {
            if(is_string($db))
                $this->db = \Yii::app()->getComponent($db);
            else {
                $this->db = $db;
            }
            if(!$this->db instanceof \CDbConnection)
                throw new \CException(\Yii::t('yii', 'The "db" parameter must be configured to be a CDbConnection object or a valid CDbConnection component id, like "db2"'));
        }
        $this->dbHelper->setDbConnection($this->db);
	$this->generatorConfigurator = new GeneratorConfigurator();
    }

    public function table($yiiTableName)
    {
        $table = $this->getRawTableName($yiiTableName);
        return parent::table($table);
    }

    private function getRawTableName($table) {
        return preg_replace('/{{(.*?)}}/',$this->db->tablePrefix.'\1',$table);
    }


}
