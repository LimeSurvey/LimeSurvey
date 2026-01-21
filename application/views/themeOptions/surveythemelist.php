
<?php
/**
 * @var $oSurveyTheme TemplateConfiguration
 */

$massiveAction = App()->getController()->renderPartial(
    '/themeOptions/_selector',
    [
        'oSurveyTheme' => $oSurveyTheme,
        'gridID' => 'themeoptions-grid',
        'dropupID' => 'themeoptions-dropup',
        'pk' => 'id'
    ],
    true,
    false
);
$this->widget('application.extensions.admin.grid.CLSGridView',
    [
        'dataProvider' => $oSurveyTheme->searchGrid(),
        'filter' => $oSurveyTheme,
        'id' => 'themeoptions-grid',
        'pager' => [
            'class' => 'application.extensions.admin.grid.CLSYiiPager',
        ],
        'massiveActionTemplate' => $massiveAction,
        'summaryText' => html_entity_decode(
    gT('Displaying {start}-{end} of {count} result(s).') . ' ' . 
    sprintf(
        gT('%s <span id="rows-per-page-label">rows per page</span>'),
        CHtml::dropDownList(
            'pageSize',
            $pageSize,
            Yii::app()->params['pageSizeOptions'],
            array(
                'class' => 'changePageSize form-select',
                'style' => 'display: inline; width: auto',
                'aria-labelledby' => 'rows-per-page-label',
            )
        )
    )
),
        'columns' => [
            [
                'id' => 'id',
                'class' => 'CCheckBoxColumn',
                'selectableRows' => '100',
            ],
            [
                'header' => gT('Preview'),
                'name' => 'preview',
                'value' => '$data->preview',
                'type' => 'raw',
                'htmlOptions' => ['class' => 'col-lg-1'],
                'filter' => false,
            ],

            [
                'header' => gT('Name'),
                'name' => 'template_name',
                'value' => '"<strong>".CHtml::encode($data->template->title)."</strong>" ."<br>" .CHtml::encode($data->template_name)',
                'htmlOptions' => ['class' => 'col-lg-2'],
                'type' => 'raw',
            ],

            [
                'header' => gT('Description'),
                'name' => 'template_description',
                'value' => '$data->description',
                'htmlOptions' => ['class' => 'col-lg-3'],
                'type' => 'raw',
            ],

            [
                'header' => gT('Type'),
                'name' => 'template_type',
                'value' => '$data->typeIcon',
                'type' => 'raw',
                'htmlOptions' => ['class' => 'col-lg-2 text-center'],
                'filter' => ['core' => 'Core theme', 'user' => 'User theme'],
            ],

            [
                'header' => gT('Extends'),
                'name' => 'template_extends',
                'value' => '$data->template->extends',
                'htmlOptions' => ['class' => 'col-lg-2 text-center'],
            ],

            [
                'header' => '',
                'name' => 'actions',
                'value' => '$data->buttons',
                'type' => 'raw',
                'htmlOptions' => ['class' => 'col-lg-1  text-center'],
                'filter' => false,
            ],

        ],
        'ajaxUpdate' => true,
        'ajaxType' => 'POST',
        'afterAjaxUpdate' => 'function(id, data){window.LS.doToolTip();bindListItemclick();LS.actionDropdown.create();}',
    ]
);
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

