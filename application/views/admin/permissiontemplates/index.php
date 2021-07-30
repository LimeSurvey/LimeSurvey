<?php
/**
 * @var PermissiontemplatesController $this
 * @var CActiveDataProvider $dataProvider
 * @var AdminController $this
 * @var CActiveDataProvider $dataProvider
 * @var Permissiontemplates $model
 * @var string $massiveAction
 */

$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('roles');

?>
<?php $this->renderPartial('permissiontemplates/partials/_menubar', []); ?>
<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            $this->widget(
                'bootstrap.widgets.TbGridView',
                [
                    'id'              => 'RoleControl--identity-gridPanel',
                    'itemsCssClass'   => 'table items',
                    'htmlOptions'     => ['style' => 'cursor: pointer;'],
                    'dataProvider'    => $model->search(),
                    'columns'         => $model->columns,
                    'filter'          => $model,
                    'ajaxType'        => 'POST',
                    'ajaxUpdate'      => 'RoleControl--identity-gridPanel',
                    'afterAjaxUpdate' => 'LS.RoleControl.bindButtons',
                    'summaryText'     => "<div class='row' style='text-align:left; color:#000'>"
                        . "<div class='col-xs-6'>" . $massiveAction . "</div>"
                        . "<div class='col-xs-6'>"
                        . gT('Displaying {start}-{end} of {count} result(s).') . ' '
                        . sprintf(
                            gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                            )
                        )
                        . "</div></div>",
                ]
            );

            ?>
        </div>
    </div>
    <div id='RoleControl-action-modal' class="modal fade RoleControl--selector--modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>
