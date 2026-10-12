<?php
/**
 * @var AdminController $this
 * @var ParticipantAttributeName $model
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsAttributeControl');

?>
<div id="pjax-content">
    <div class="row">
        <?php
        require_once Yii::getPathOfAlias('application.extensions.admin.grid.FloatingActionsWidget.actions.AttributeListMassiveActions') . '.php';
        $floatingActions = \actions\AttributeListMassiveActions::getActions();
        $this->widget('ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget', [
            'pk'       => 'selectedAttributeNames',
            'gridId'   => 'list_attributes',
            'aActions' => $floatingActions,
        ]);

        $this->widget('application.extensions.admin.grid.CLSGridView', [
            'id' => 'list_attributes',
            'dataProvider' => $model->search(),
            'columns' => $model->columns,
            'filter' => $model,
            'rowHtmlOptionsExpression' => '["data-attribute_id" => $data->attribute_id]',
            'lsShowSelectionBar'         => false,
            'emptyText'                => gT('No attributes found.'),
            'lsAfterAjaxUpdate' => ['LS.CPDB.bindButtons();', 'LS.CPDB.attributePanel();'],
            'lsPageSizeCurrentValue'    => Yii::app()->user->getState('pageSizeAttributes', Yii::app()->params['defaultPageSize']),
        ]);
        ?>
    </div>
</div>
<span id="locator" data-location="attributes">&nbsp;</span>