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
        } else {
            // trigger action_dropdown() as a default although no lsAfterAjaxUpdate param passed.
            // this method is useful for preventing action dropdown cut off && overlapped in other browsers like firefox
            $this->afterAjaxUpdate = 'function(){ action_dropdown(); }';
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

        // ========== this is added for pagination size working by referencing from old limegridview  ==============
        $id = $this->getId();

        if ($this->ajaxUpdate) {
            $ajaxUpdate = array_unique(preg_split('/\s*,\s*/', $this->ajaxUpdate . ',' . $id, -1, PREG_SPLIT_NO_EMPTY));
        } else {
            $ajaxUpdate = false;
        }

        $options = array(
            'ajaxUpdate' => $ajaxUpdate,
            'ajaxVar' => $this->ajaxVar,
            'pagerClass' => $this->pagerCssClass,
            'loadingClass' => $this->loadingCssClass,
            'filterClass' => $this->filterCssClass,
            'tableClass' => $this->itemsCssClass,
            'selectableRows' => $this->selectableRows,
            'enableHistory' => $this->enableHistory,
            'updateSelector' => $this->updateSelector,
            'filterSelector' => $this->filterSelector
        );

        $options = CJavaScript::encode($options);

        $cs = Yii::app()->getClientScript();
        $cs->registerScript(__CLASS__ . '#' . $id, "jQuery('#$id').yiiGridView($options);", LSYii_ClientScript::POS_POSTSCRIPT);

        // ====================================================================================================


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
