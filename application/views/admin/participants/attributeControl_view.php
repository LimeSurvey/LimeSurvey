<?php
/**
 * @var AdminController $this
 * @var ParticipantAttributeName $model
 * @var string $massiveAction
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsAttributeControl');

?>
<div id="pjax-content">
    <div class="row">
        <?php
        $this->widget('application.extensions.admin.grid.CLSGridView', [
            'id' => 'list_attributes',
            'dataProvider' => $model->search(),
            'columns' => $model->columns,
            'filter' => $model,
            'rowHtmlOptionsExpression' => '["data-attribute_id" => $data->attribute_id]',
            'massiveActionTemplate' => $massiveAction,
            'emptyText'                => gT('No attributes found.'),
            'lsAfterAjaxUpdate' => ['LS.CPDB.bindButtons();', 'LS.CPDB.attributePanel();', 'switchStatusOfListActions();'],
            'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSizeAttributes',
                        Yii::app()->user->getState('pageSizeAttributes', Yii::app()->params['defaultPageSize']),
                        App()->params['pageSizeOptions'],
                        array('class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto')
                    )
                ),
        ]);
        ?>
    </div>
</div>
<span id="locator" data-location="attributes">&nbsp;</span>
