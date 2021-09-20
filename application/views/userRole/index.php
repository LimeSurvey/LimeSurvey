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
<?php $this->renderPartial('partials/_menubar', []); ?>
<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            $this->widget(
                'bootstrap.widgets.TbGridView',
                [
                    'id'              => 'RoleControl--identity-gridPanel',
                    'htmlOptions'     => ['class' => 'table-responsive grid-view-ls'],
                    'dataProvider'    => $model->search(),
                    'columns'         => $model->columns,
                    'filter'          => $model,
                    'ajaxType'        => 'POST',
                    'ajaxUpdate'      => 'RoleControl--identity-gridPanel',
                    'afterAjaxUpdate' => 'LS.RoleControl.bindButtons',
                    'template'        => "{items}\n<div id='rolecontrolListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                        . sprintf(
                            gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                ['class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto']
                            )
                        ),
                ]
            );

            ?>
        </div>
    </div>
    <div id='RoleControl-action-modal' class="modal fade RoleControl--selector--modal" tabindex="-1" role="dialog">
        <div id="userrole-modal-dialog" class="modal-dialog" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>
