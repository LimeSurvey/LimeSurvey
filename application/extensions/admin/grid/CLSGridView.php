<?php

Yii::import('zii.widgets.grid.CGridView');

class CLSGridView extends CGridView
{

    /**
     * @var string
     */
    public $massiveActionTemplate = '';

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();

        $this->pager = ['class' => 'application.extensions.admin.grid.CLSYiiPager'];
        $this->htmlOptions['class'] = '';
        $classes = array('table', 'table-hover');
        $this->template = "{items}\n<div class=\"row mx-auto\" id='userListPager'><div class=\"col-md-4\" id=\"massive-action-container\">$this->massiveActionTemplate</div><div class=\"col-md-4 \">{pager}</div><div class=\"col-md-4 summary-container\">{summary}</div></div>";
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
