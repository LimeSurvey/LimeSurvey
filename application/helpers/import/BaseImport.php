<?php
namespace ls\import;
abstract class BaseImport {
    public function __construct() {}

    abstract public function setSource($file);

    /**
     * @return \Survey
     */
    abstract public function run();


}