<?php
namespace ls\import;
abstract class BaseImport {
    public function __construct() {}

    abstract public function setSource($file);

    /**
     * @return \ls\models\Survey
     */
    abstract public function run();


}