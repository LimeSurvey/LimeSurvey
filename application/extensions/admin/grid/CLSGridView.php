<?php

Yii::import('zii.widgets.grid.CGridView');

class CLSGridView extends CGridView
{
    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        $classes = array('table');
        /*
        if (isset($this->type) && !empty($this->type)) {
            if (is_string($this->type)) {
                $this->type = explode(' ', $this->type);
            }

            foreach ($this->type as $type) {
                $classes[] = 'table-' . $type;
            }
        }*/
        if (!empty($classes)) {
            $classes = implode(' ', $classes);
            if (isset($this->itemsCssClass)) {
                $this->itemsCssClass .= ' ' . $classes;
            } else {
                $this->itemsCssClass = $classes;
            }
        }
    }
}
