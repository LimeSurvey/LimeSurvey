<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('usersIndex');

?>

<?php if(!Permission::model()->hasGlobalPermission('users', 'read')) :?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><?=gT("You don't have permission to enter this page!")?></h2>
        </div>
    </div>
</div>
<?php App()->end();?>
<?php endif; ?>


<div class="menubar surveymanagerbar">
    <div class="row container-fluid">
        <div class="col-xs-12 col-md-12">
            <div class="h2"><?php eT("User management panel")?></div>
        </div>
    </div>
</div>
<div class='menubar surveybar' id="usermanagementbar">
    <div class='row'>

        <div class="col-md-9">
            <?php if(Permission::model()->hasGlobalPermission('users', 'create')): ?>
                <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/editusermodal")?>" data-toggle="modal" title="<?php eT('Add a new survey administrator'); ?>" class="btn btn-default UserManagement--action--openmodal">
                    <i class="fa fa-plus-circle text-success"></i> <?php eT("Add user");?>
                </button>
                <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/adddummyuser")?>" data-toggle="modal" title="<?php eT('Add a new survey administrator with random values'); ?>" class="btn btn-default UserManagement--action--openmodal">
                    <i class="fa fa-plus-square text-success"></i> <?=gT('Add dummy user')?>
                </button>
                <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/importuser")?>" data-toggle="modal" title="<?php eT('Import survey administrators'); ?>" class="btn btn-default UserManagement--action--openmodal">
                    <i class="fa fa-upload text-success"></i> <?php eT("Import (CSV)");?>
                </button>
            <?php endif; ?>
            <?php if(Permission::model()->hasGlobalPermission('users', 'export')): ?>
                <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/exportusers")?>" data-toggle="modal" title="<?php eT('Export survey administrators'); ?>" class="btn btn-default UserManagement--action--openmodal">
                    <i class="fa fa-upload text-success"></i> <?php eT("Export (CSV)");?>
                </button>
            <?php endif; ?>
        </div>

        <div class="col-md-3 text-right">
            <a class="btn btn-default" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                <span class="fa fa-backward"></span>
                &nbsp;
                <?php eT('Return to admin home'); ?>
            </a>
        </div>
    </div>
</div>
<div class="pagetitle h3"><?php eT("User control");?></div>
<div class="row" style="margin-bottom: 100px">
    <div class="container-fluid">
        <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'id' => 'usermanagement--identity-gridPanel',
                'itemsCssClass' => 'table table-striped items',
                'dataProvider' => $model->search(),
                'columns' => $columnDefinition,
                'filter' => $model,
                'afterAjaxUpdate' => 'LS.UserManagement.bindButtons',
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
                    $.fn.yiiGridView.update('usermanagement--identity-gridPanel',{ data:{ pageSize: $(this).val() }});
                });
            });
        </script>
</div>
<div id='UserManagement-action-modal' class="modal fade UserManagement--selector--modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
