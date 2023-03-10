<?php

Yii::import('zii.widgets.grid.CGridView');

class CLSGridView extends TbGridView
{
    /**
     * @var string
     */
    public $massiveActionTemplate = '';

    /**
     * An array of Javascript functions that will be passed to afterAjaxUpdate
     * @var array
     */
    public $lsAfterAjaxUpdate;

    /**
     * Initializes the widget.
     * @throws CException
     */
    public function init()
    {
        parent::init();
        $this->registerGridviewScripts();

        $this->pager = ['class' => 'application.extensions.admin.grid.CLSYiiPager'];
        $this->htmlOptions['class'] = '';
        $classes = ['table', 'table-hover'];
        $this->template = $this->render('template', ['massiveActionTemplate' => $this->massiveActionTemplate], true);
        $this->lsAfterAjaxUpdate();
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

    /**
     * parse javascript snippets to TbGridView's afterAjaxUpdate and insert global javascript snippets for griviews
     * @return void
     */
    protected function lsAfterAjaxUpdate(): void
    {
        if (isset($this->lsAfterAjaxUpdate)) {
            $this->afterAjaxUpdate = 'function(id, data){';
            foreach ($this->lsAfterAjaxUpdate as $jsCode) {
                $this->afterAjaxUpdate .= $jsCode;
            }
            $this->afterAjaxUpdate .= 'action_dropdown();';
            $this->afterAjaxUpdate .= '}';
        }
    }

    private function registerGridviewScripts()
    {
        // Scrollbar
        App()->clientScript->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/assets/gridScrollbar.js',
            CClientScript::POS_BEGIN,
            ['test321' => "something"]
        );

        // changePageSize
        $script = '
			jQuery(document).on("change", "#' . $this->id . ' .changePageSize", function(){
				var pageSizeName = $(this).attr("name");
				if (!pageSizeName) {
					pageSizeName = "pageSize";
				}
				var data = $("#' . $this->id . ' .filters input, #' . $this->id . ' .filters select").serialize();
				data += (data ? "&" : "") + pageSizeName + "=" + $(this).val();
				$.fn.yiiGridView.update("' . $this->id . '", {data: data});
			});
		';
        App()->getClientScript()->registerScript('pageChanger#' . $this->id, $script, LSYii_ClientScript::POS_POSTSCRIPT);
    }
}
