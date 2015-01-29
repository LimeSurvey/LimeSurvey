<?php

class MemoryValidator extends CNumberValidator 
{
    /**
	 * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
	 */
	public $max;
	/**
	 * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
	 */
	public $min;
	/**
	 * @var string user-defined error message used when the value is too big.
	 */
	public $tooBig;
	/**
	 * @var string user-defined error message used when the value is too small.
	 */
	public $tooSmall;
    
    /**
	 * @var string the regular expression for matching integers.
	 * @since 1.1.7
	 */
	public $memoryPattern ='/^\s*(\d+)\s*([A..Z])*\s*$/';
	
    protected function validateAttribute($object, $attribute) 
    {
        $value = $this->toBytes($object->$attribute);
        if($value > -1 && $this->min !== null && $value < $this->toBytes($this->min))
		{   
			$message=$this->tooSmall!==null?$this->tooSmall:Yii::t('yii','{attribute} is too small (minimum is {min}).');
            $this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		if($this->max!==null && $value > $this->toBytes($this->max))
		{
			$message=$this->tooBig!==null?$this->tooBig:Yii::t('yii','{attribute} is too big (maximum is {max}).');
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
    }
    
    private function toBytes($value) {
        $this->memoryPattern = '/^\s*(\d+)\s*([A-Za-z])\s*$/';
        if (preg_match($this->memoryPattern, $value, $matches) == 0) {
            return $value;
        }
         $multipliers = [
            'M' => 1024 * 1024
        ];
        return !isset($matches[2]) ? $matches[1] : $matches[1] * $multipliers[strtoupper($matches[2])];
        
    }

}
