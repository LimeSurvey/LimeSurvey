<?php

class ResponseGridView extends TbGridView
{
    /* @var SurveyDynamic $model */
    public $model;

    /* @var Survey $survey */
    public $survey;

    public $language;
    public $pageSize = 15;

    public function init()
    {
        $massiveAction = $this->getController()->renderPartial('/admin/responses/massive_actions/_selector', array(), true);

        $aColumns = array();

        $aColumns[] = array(
            'id' => 'id',
            'class' => 'CCheckBoxColumn',
            'selectableRows' => '100'
        );

        $aColumns[] = array(
            'class' => 'application.widgets.grid.ResponseGridButtonColumn',
            'survey' => $this->survey,
            'buttons' => $this->model->getGridButtons(),
        );

        $aColumns[] = array(
            'header' => 'id',
            'name' => 'id'
        );

        $aColumns[] = array(
            'header' => 'seed',
            'name' => 'seed'
        );

        $aColumns[] = array(
            'header' => 'lastpage',
            'name' => 'lastpage',
            'type' => 'number',
            'filter' => TbHtml::textField('SurveyDynamic[lastpage]', $this->model->lastpage)
        );

        $aColumns[] = array(
            'header' => gT("completed"),
            'name' => 'completed_filter',
            'value' => '$data->completed',
            'type' => 'raw',
            'filter' => TbHtml::dropDownList('SurveyDynamic[completed_filter]', $this->model->completed_filter, array('' => gT('All'), 'Y' => gT('Yes'), 'N' => gT('No')))
        );

        $bHaveToken = $this->survey->anonymized == "N" && tableExists('tokens_' . $this->survey->sid) && Permission::model()->hasSurveyPermission($this->survey->sid, 'tokens', 'read');
        if ($bHaveToken) {

            $aColumns[] = array(
                'header' => 'token',
                'name' => 'token',
                'type' => 'raw',
                'value' => '$data->tokenForGrid'
            );

            $aColumns[] = array(
                'header' => gT("First name"),
                'name' => 'tokens.firstname',
                'id' => 'firstname',
                'type' => 'raw',
                'value' => '$data->firstNameForGrid',
                'filter' => TbHtml::textField('SurveyDynamic[firstname_filter]', $this->model->firstname_filter)
            );

            $aColumns[] = array(
                'header' => gT("Last name"),
                'name' => 'tokens.lastname',
                'type' => 'raw',
                'id' => 'lastname',
                'value' => '$data->lastNameForGrid',
                'filter' => TbHtml::textField('SurveyDynamic[lastname_filter]', $this->model->lastname_filter)
            );

            $aColumns[] = array(
                'header' => gT("Email"),
                'name' => 'tokens.email',
                'id' => 'email',
                'filter' => TbHtml::textField('SurveyDynamic[email_filter]', $this->model->email_filter)
            );
        }

        $aColumns[] = array(
            'header' => 'startlanguage',
            'name' => 'startlanguage'
        );

        $fieldmap = createFieldMap($this->survey, 'full', true, false, $this->language);
        foreach ($this->model->metaData->columns as $column) {
            if (!in_array($column->name, $this->model->getDefaultColumns())) {
                $colName            = viewHelper::getFieldCode($fieldmap[$column->name], array('LEMcompat' => true));
                $base64jsonFieldMap = base64_encode(json_encode($fieldmap[$column->name]));
                $colDetails         = viewHelper::getFieldText($fieldmap[$column->name], array('abbreviated' => $this->model->ellipsize_header_value, 'separator'=>array('<br>','')));
                $colTitle           = viewHelper::getFieldText($fieldmap[$column->name], array('afterquestion' => "<hr>"));

                $aColumns[] = array(
                    'header' => '<span data-toggle="popover" data-trigger="hover focus" data-placement="bottom" title="' . $colName . '" data-content="' . $colTitle . '" data-html="1">' . $colName . ' <br/> ' . $colDetails . '</span>',
                    'headerHtmlOptions'=>array('style'=>'min-width: 350px;'),
                    'name' => $column->name,
                    'type' => 'raw',
                    'value' => '$data->getExtendedData("' . $column->name . '", "' . $this->language . '", "' . $base64jsonFieldMap . '")',
                );
            }
        }

        $this->dataProvider     = $this->model->search();
        $this->filter           = $this->model;
        $this->columns          = $aColumns;

        $this->itemsCssClass    = 'table-striped';
        $this->id               = 'responses-grid';

        $this->ajaxUpdate       = 'responses-grid';
        $this->ajaxType         = 'POST';
        $this->afterAjaxUpdate  = 'js:function(id, data){ LS.resp.bindScrollWrapper(); onUpdateTokenGrid();$(".grid-view [data-toggle=\'popover\']").popover({container:\'body\'}); }';

        $this->template         = "<div class='push-grid-pager'>{items}\n</div><div id='ListPager'><div class=\"col-sm-12\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-12 pager-container ls-ba \">{pager}</div><div class=\"col-sm-12 summary-container\">{summary}</div></div>";
        $this->summaryText      = gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSize',
                $this->pageSize,
                Yii::app()->params['pageSizeOptions'],
                array(
                    'class' => 'changePageSize form-control',
                    'style'=> 'display: inline; width: auto'
                )
            ));

        parent::init();
    }
}
