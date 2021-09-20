<?php
/* @var $this AdminController */
/* @var $model CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('2faUsersIndex');

?>
<?php if(!Permission::model()->hasGlobalPermission('users', 'update')) :?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><?=gT("No permission")?></h2>
        </div>
    </div>
</div>
<?php App()->end();?>
<?php endif; ?>
<?php
    App()->getClientScript()->registerScript('TFA-Management-wrap', 'window.TFA = window.TFA || new TFAUserManagementClass();', LSYii_ClientScript::POS_BEGIN);
?>

<div class="container-fluid ls-space padding left-50 right-50">
    <div class="row">
        <div class="col-xs-12 h1 pagetitle">
            2-Factor-Authentication | User management
        </div>
    </div>
    <div class="row">
    <?php if(Permission::model()->hasGlobalPermission('superadmin', 'read')): ?>
        <div class="row" style="margin-bottom: 100px">
            <div class="container-fluid">
                <?php
                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'id' => 'tfa-usermanagement-gridPanel',
                        'itemsCssClass' => 'table table-striped items',
                        'dataProvider' => $model->search(),
                        'columns' => $model->colums,
                        'filter' => $model,
                        'afterAjaxUpdate' => 'window.TFA.bind',
                        'htmlOptions' => ['class' => 'table-responsive grid-view-ls'],
                        'summaryText'   => "<div class='row'>"
                        ."<div class='col-xs-6'></div>"
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
                    window.TFA.bind();
	            jQuery(function($) {
                      jQuery(document).on("change", '#pageSize', function(){
                          $.fn.yiiGridView.update('tfa-usermanagement-gridPanel',{ data:{ pageSize: $(this).val() }});
                      });
                    });
                </script>
        </div>
    <?php endif; ?>
    </div>
</div>
