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
     * string for a link that is on every row
     * @var string
     */
    public $rowLink;

    /**
     * Initializes the widget.
     * @throws CException
     */
    public function init()
    {
        parent::init();

        $this->pager = ['class' => 'application.extensions.admin.grid.CLSYiiPager'];
        $this->htmlOptions['class'] = 'grid-view-ls';
        $classes = ['table', 'table-hover'];
        $this->template = $this->render('template', ['massiveActionTemplate' => $this->massiveActionTemplate], true);
        $this->rowLink();
        $this->lsAfterAjaxUpdate();
        if (!empty($classes)) {
            $classes = implode(' ', $classes);
            if (isset($this->itemsCssClass)) {
                $this->itemsCssClass .= ' ' . $classes;
            } else {
                $this->itemsCssClass = $classes;
            }
        }
        $this->registerGridviewScripts();
    }

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns()
    {
        $this->appendFilteredColumns();
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
        // this will override afterAjaxUpdate if lsAfterAjaxUpdate is defined
        // please do not override afterAjaxUpdate by default to keep compatibility with base functionality of yii
        if (isset($this->lsAfterAjaxUpdate)) {
            $this->afterAjaxUpdate = 'function(id, data){';
            foreach ($this->lsAfterAjaxUpdate as $jsCode) {
                $this->afterAjaxUpdate .= $jsCode;
            }
            $this->afterAjaxUpdate .= 'LS.actionDropdown.create();';
            $this->afterAjaxUpdate .= 'LS.rowlink.create();';
            $this->afterAjaxUpdate .= '}';
        }
    }

    /**
     * Adds the data-rowlink attribute to $this->rowHtmlOptionsExpression to be used by the rowLink.js
     * The JS adds a link to every td element of the row
     * @return void
     */
    protected function rowLink(): void
    {
        if (!empty($this->rowLink) && empty($this->rowHtmlOptionsExpression)) {
            $this->rowHtmlOptionsExpression = function ($row, $data, $grid) {
                $options = [];
                $options['data-rowlink'] = eval('return ' . $this->rowLink . ';');
                return $options;
            };
        }
    }

    private function registerGridviewScripts()
    {
        // Scrollbar
        App()->clientScript->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/assets/gridScrollbar.js',
            CClientScript::POS_BEGIN
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

    /**
     * Registers necessary client scripts.
     * customization for CLSGridview
     * @throws CException
     */
    public function registerClientScript()
    {
        // ========== this is added for pagination size working by referencing from old limegridview  ==============
        $id = $this->getId();

        if ($this->ajaxUpdate === false) {
            $ajaxUpdate = false;
        } else {
            $ajaxUpdate = array_unique(preg_split('/\s*,\s*/', $this->ajaxUpdate . ',' . $id, -1, PREG_SPLIT_NO_EMPTY));
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
        if ($this->ajaxUrl !== null) {
            $options['url'] = CHtml::normalizeUrl($this->ajaxUrl);
        }
        if ($this->ajaxType !== null) {
            $options['ajaxType'] = strtoupper($this->ajaxType);
            $request = Yii::app()->getRequest();
            if ($options['ajaxType'] == 'POST' && $request->enableCsrfValidation) {
                $options['csrfTokenName'] = $request->csrfTokenName;
                $options['csrfToken'] = $request->getCsrfToken();
            }
        }
        if ($this->enablePagination) {
            $options['pageVar'] = $this->dataProvider->getPagination()->pageVar;
        }
        foreach (array('beforeAjaxUpdate', 'afterAjaxUpdate', 'ajaxUpdateError', 'selectionChanged') as $event) {
            if ($this->$event !== null) {
                if ($this->$event instanceof CJavaScriptExpression) {
                    $options[$event] = $this->$event;
                } else {
                    $options[$event] = new CJavaScriptExpression($this->$event);
                }
            }
        }

        $options = CJavaScript::encode($options);
        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('bbq');
        if ($this->enableHistory) {
            $cs->registerCoreScript('history');
        }
        $cs->registerScriptFile($this->baseScriptUrl . '/jquery.yiigridview.js', CClientScript::POS_END);
        $cs->registerScript(
            __CLASS__ . '#' . $id,
            "jQuery('#$id').yiiGridView($options);",
            LSYii_ClientScript::POS_POSTSCRIPT
        );
    }

    protected function appendFilteredColumns(): void
    {
        if (App()->request->getQuery('columnFilter') == 'empty' && $this->ajaxUpdate == App()->request->getQuery('ajax')) {
            SettingsUser::setUserSetting('column_filter_' . $this->ajaxUpdate, '');
            SettingsUser::setUserSetting('column_filter_model_' . $this->ajaxUpdate, '');
        } elseif (App()->request->getQuery('columnFilter') !== null && $this->ajaxUpdate == App()->request->getQuery('ajax')) {
            $model = App()->request->getParam('model');
            $submitted_column_filter = App()->request->getParam('columnFilter');
            SettingsUser::setUserSetting('column_filter_' . $this->ajaxUpdate, implode('|', $submitted_column_filter));
            SettingsUser::setUserSetting('column_filter_model_' . $this->ajaxUpdate, $model);
            $this->addColumns($model, $submitted_column_filter);
        } elseif ($column_filter = SettingsUser::getUserSettingValue('column_filter_' . $this->ajaxUpdate)) {
            $model = SettingsUser::getUserSettingValue('column_filter_model_' . $this->ajaxUpdate);
            $column_filter = explode('|', $column_filter);
            $this->addColumns($model, $column_filter);
        }
    }

    protected function addColumns($model, $columns_list): void
    {
        if (!empty($model) && method_exists($model::model(), 'getFilterableColumns')) {
            list($columns, $filterableColumns) = $model::model()->getFilterableColumns();
            foreach ($columns_list as $filter) {
                $column_data = null;
                if (isset($filterableColumns[$filter])) {
                    $column_data = $filterableColumns[$filter];
                }
                if (is_array($column_data)) {
                    array_splice($this->columns, count($this->columns) - 2, 0, [$column_data]);
                }
            }
        }
    }
}
