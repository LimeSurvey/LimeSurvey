<?php
/* @var $this PermissiontemplatesController */
/* @var $dataProvider CActiveDataProvider */

?>

<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// $this->breadcrumbs=array(
//     'Surveymenus',
// );

// $this->menu=array(
//     array('label'=>'Create Surveymenu', 'url'=>array('create')),
//     array('label'=>'Manage Surveymenu', 'url'=>array('admin')),
// );
//
$pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
$massiveAction = '';
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('roles');

?>
<?php $this->renderPartial('permissiontemplates/partials/_menubar', []); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 h1 pagetitle">
            <?php eT('Permission roles')?>
        </div>
    </div>
    <div class="row" style="margin-bottom: 100px">
        <div class="container-fluid">
            <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'id' => 'RoleControl--identity-gridPanel',
                    'itemsCssClass' => 'table table-striped items',
                    'dataProvider' => $model->search(),
                    'columns' => $model->columns,
                    'filter' => $model,
                    'afterAjaxUpdate' => 'LS.RoleControl.bindButtons',
                    'summaryText'   => "<div class='row'>"
                    ."<div class='col-xs-6'>".$massiveAction."</div>"
                    ."<div class='col-xs-6'>"
                    .gT('Displaying {start}-{end} of {count} result(s).').' '
                        . sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                        )
                    ."</div></div>",
                    ));

                ?>
            </div>

            <!-- To update rows per page via ajax -->
            <script type="text/javascript">
                jQuery(function($) {
                    jQuery(document).on("change", '#pageSize', function(){
                        $.fn.yiiGridView.update('RoleControl--identity-gridPanel',{ data:{ pageSize: $(this).val() }});
                    });
                });
            </script>
    </div>
    <div id='RoleControl-action-modal' class="modal fade RoleControl--selector--modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>

