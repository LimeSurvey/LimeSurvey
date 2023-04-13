<?php
/**
 * @var UserRoleController $this
 * @var CActiveDataProvider $dataProvider
 * @var Permissiontemplates $model
 * @var string $massiveAction
 */

$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('roles');

?>
<?php //$this->renderPartial('partials/_menubar', []); ?>
<div class="col-12">
    <div class="row">
        <div class="col-12">
            <?php
            $this->widget(
                'application.extensions.admin.grid.CLSGridView',
                [
                    'id' => 'RoleControl--identity-gridPanel',
                    'htmlOptions' => ['class' => 'table-responsive grid-view-ls'],
                    'dataProvider' => $model->search(),
                    'columns' => $model->columns,
                    'filter' => $model,
                    'massiveActionTemplate' => $massiveAction,
                    'ajaxType' => 'POST',
                    'ajaxUpdate' => 'RoleControl--identity-gridPanel',
                    'afterAjaxUpdate' => 'LS.RoleControl.bindButtons',
                    'pager' => [
                        'class' => 'application.extensions.admin.grid.CLSYiiPager',
                    ],
                    'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                        . sprintf(
                            gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                App()->params['pageSizeOptions'],
                                array('class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto')
                            )
                        ),
                ]
            );
            ?>
        </div>
    </div>
    <div id='RoleControl-action-modal' class="modal fade RoleControl--selector--modal" tabindex="-1" role="dialog">
        <div id="userrole-modal-dialog" class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>
