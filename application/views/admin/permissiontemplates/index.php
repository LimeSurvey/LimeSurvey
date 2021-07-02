<?php
/* @var $this PermissiontemplatesController */
/* @var $dataProvider CActiveDataProvider */

?>

<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('roles');

?>
<?php $this->renderPartial('permissiontemplates/partials/_menubar', []); ?>
<div class="container-fluid">
    <div class="row" style="margin-top: 10px; margin-bottom: 100px">
        <div class="container-fluid">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'id'              => 'RoleControl--identity-gridPanel',
                'itemsCssClass'   => 'table table-striped items',
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
                    . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto'))
                    )
                    . "</div></div>",
            ));

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
