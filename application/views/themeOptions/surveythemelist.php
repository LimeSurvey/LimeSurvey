<?php
$massiveAction = App()->getController()->renderPartial(
    '/themeOptions/_selector',
    [
        'oSurveyTheme' => $oSurveyTheme,
        'gridID'       => 'themeoptions-grid',
        'dropupID'     => 'themeoptions-dropup',
        'pk'           => 'id'
    ],
    true,
    false);
$this->widget('yiistrap.widgets.TbGridView',
    [
        'dataProvider'    => $oSurveyTheme->searchGrid(),
        'filter'          => $oSurveyTheme,
        'id'              => 'themeoptions-grid',
        'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
        'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                "<div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div>",
                CHtml::dropDownList(
                    'pageSize',
                    $pageSize,
                    Yii::app()->params['pageSizeOptions'],
                    ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                )
            ),
        'columns'         => [
            [
                'id'             => 'id',
                'class'          => 'CCheckBoxColumn',
                'selectableRows' => '100',
            ],
            [
                'header'      => gT('Preview'),
                'name'        => 'preview',
                'value'       => '$data->preview',
                'type'        => 'raw',
                'htmlOptions' => ['class' => 'col-md-1'],
                'filter'      => false,
            ],

            [
                'header'      => gT('Name'),
                'name'        => 'template_name',
                'value'       => '$data->template_name',
                'htmlOptions' => ['class' => 'col-md-2'],
            ],

            [
                'header'      => gT('Description'),
                'name'        => 'template_description',
                'value'       => '$data->description',
                'htmlOptions' => ['class' => 'col-md-3'],
                'type'        => 'raw',
            ],

            [
                'header'      => gT('Type'),
                'name'        => 'template_type',
                'value'       => '$data->typeIcon',
                'type'        => 'raw',
                'htmlOptions' => ['class' => 'col-md-2'],
                'filter'      => ['core' => 'Core theme', 'user' => 'User theme'],
            ],

            [
                'header'      => gT('Extends'),
                'name'        => 'template_extends',
                'value'       => '$data->template->extends',
                'htmlOptions' => ['class' => 'col-md-2'],
            ],

            [
                'header'      => '',
                'name'        => 'actions',
                'value'       => '$data->buttons',
                'type'        => 'raw',
                'htmlOptions' => ['class' => 'col-md-1'],
                'filter'      => false,
            ],

        ],
        'ajaxUpdate'      => true,
        'ajaxType'        => 'POST',
        'afterAjaxUpdate' => 'function(id, data){window.LS.doToolTip();bindListItemclick();}',
    ]);
?>

<!-- To update rows per page via ajax setSession-->
<?php
$script = '
                jQuery(document).on("change", "#pageSize", function(){
                    $.fn.yiiGridView.update("themeoptions-grid",{ data:{ pageSize: $(this).val() }});
                });
                ';
App()->getClientScript()->registerScript('themeoptions-grid', $script, LSYii_ClientScript::POS_POSTSCRIPT);
?>