<?php

Yii::import('zii.widgets.grid.CGridView');

class CLSGridView extends TbGridView
{
    /**
     * @var string
     */
    public $massiveActionTemplate = '';

    /**
     * Initializes the widget.
     * @throws CException
     */
    public function init()
    {
        parent::init();
        App()->clientScript->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/assets/gridScrollbar.js',
            CClientScript::POS_BEGIN
        );

        $this->pager = ['class' => 'application.extensions.admin.grid.CLSYiiPager'];
        $this->htmlOptions['class'] = '';
        $classes = array('table', 'table-hover');
        $this->template = $this->render('template', ['massiveActionTemplate' => $this->massiveActionTemplate], true);

        if (!empty($classes)) {
            $classes = implode(' ', $classes);
            if (isset($this->itemsCssClass)) {
                $this->itemsCssClass .= ' ' . $classes;
            } else {
                $this->itemsCssClass = $classes;
            }
        }
    }

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns()
    {
        foreach ($this->columns as $i => $column) {
            if (is_array($column) && !isset($column['class'])) {
                $this->columns[$i]['class'] = '\TbDataColumn';
            }
        }
        parent::initColumns();
    }
}
