<?php

class WritableValidator extends CValidator
{
    /**
     * Check recursively, has no effect when attribute points to a file.
     * @var boolean
     */
    public $recursive = false;
    
    /**
     * If true, validation will fail if attribute points to a file instead of 
     * a directory.
     * @var boolean
     */
    public $forceDirectory = false;
    
    protected function validateAttribute($object, $attribute) 
    {
        $value = $object->$attribute;
        if (!file_exists($value)) {
            $this->addError($object, $attribute, gT("File or directory does not exist."));
            return;
        }
        
        if (is_file($value) && $this->forceDirectory) {
            $this->addError($object, $attribute, gT("Path points to a file not a directory."));
            return;
        }
        
        $this->checkWritable($value, $object, $attribute);
    }
    
    protected function checkWritable($path, $object, $attribute) {
        if (!is_writable($path)) {
            $this->addError($object, $attribute, gT("Path is not writable."));
            return false;
        }
        
        if (is_dir($path) && $this->recursive) {
            foreach (scandir($path) as $childPath) {
                if ($childPath != '.' && $childPath != '..') {
                    if (!$this->checkWritable($childPath, $object, $attribute)) {
                        break;
                    }
                }
            }
        }
        
    }

}