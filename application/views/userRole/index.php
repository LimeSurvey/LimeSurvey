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
                    'caption' => gT('User roles'),
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
                    'summaryText' => html_entity_decode(
                        gT('Displaying {start}-{end} of {count} result(s).') . ' ' .
                        sprintf(
                            '%s %s',
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                App()->params['pageSizeOptions'],
                                [
                                    'class' => 'changePageSize form-select',
                                    'style' => 'display: inline; width: auto',
                                    'aria-labelledby' => 'RoleControl--identity-gridPanel-rows-per-page-label',
                                ]
                            ),
                            CHtml::tag(
                                'span',
                                ['id' => 'RoleControl--identity-gridPanel-rows-per-page-label'],
                                CHtml::encode(gT('rows per page'))
                            )
                        )
                    ),
                ]
            );
            ?>
        </div>
    </div>
    <div id="RoleControl-action-modal" class="modal fade RoleControl--selector--modal" tabindex="-1" role="dialog" aria-modal="true">
        <div id="userrole-modal-dialog" class="modal-dialog modal-lg">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>
