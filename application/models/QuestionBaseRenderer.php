<?php

/**
 * abstract Class QuestionTypeRoot
 * The ia Array contains the following
 *  0 => string qid 
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type 
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 * {@inheritdoc}
 */
abstract class QuestionRenderer extends StaticModel
{
    public $fieldArray;
    public $sHtml;
    public $bRenderDirect;
    
    public function __construct($fieldArray, $bRenderDirect = false)
    {
        $this->fieldArray = $fieldArray;
        $this->bRenderDirect = $bRenderDirect;
    }
    
    
    abstract public function getMainView();
    abstract public function getSGQA();
    abstract public function createFieldArray();

    abstract public function render();
}
