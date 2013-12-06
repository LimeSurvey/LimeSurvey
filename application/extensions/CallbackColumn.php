<?php
    Yii::import('zii.widgets.grid.CGridColumn');
    class CallbackColumn extends CGridColumn
    {
        
        public $label;
        
        public $url;
        
        public function renderDataCellContent($row, $data) 
        {
            if (isset($this->label) && is_callable($this->label))
            {
                $text = call_user_func($this->label, $data);
            }
            else
            {
                $text = $data[$this->label];
            }
            // Create link.
            if (isset($this->url))
            {
                if (is_callable($this->url))
                {
                    $url = call_user_func($this->url, $data);
                }
                else
                {
                    $url = $this->url;
                }
                echo CHtml::link($text, $url);
            }
            else
            {
                echo $text;
            }
                
            
        }
    }
?>
