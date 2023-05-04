<?php
/* @var $this AdminController */
/* @var $model CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('2faUsersIndex');

?>
<?php if(!Permission::model()->hasGlobalPermission('users', 'update')) :?>
<div class="row">
    <div class="col-12">
        <h2><?=gT("No permission")?></h2>
    </div>
</div>
<?php App()->end();?>
<?php endif; ?>
<?php
    App()->getClientScript()->registerScript('TFA-Management-wrap', 'window.TFA = window.TFA || new TFAUserManagementClass();', LSYii_ClientScript::POS_BEGIN);
?>

<div class="h1 pagetitle">
    2-Factor-Authentication | User management
</div>
<?php if(Permission::model()->hasGlobalPermission('superadmin', 'read')): ?>
    <?php
    $this->widget('application.extensions.admin.grid.CLSGridView', [
        'id'              => 'tfa-usermanagement-gridPanel',
        'itemsCssClass'   => 'table table-striped items',
        'dataProvider'    => $model->search(),
        'columns'         => $model->getColumns(),
        'filter'          => $model,
        'afterAjaxUpdate' => 'window.TFA.bind',
        'summaryText'     => "<div class='row'>"
            . "<div class='col-6'></div>"
            . "<div class='col-6'>"
            . gT('Displaying {start}-{end} of {count} result(s).') . ' '
            . sprintf(gT('%s rows per page'),
                CHtml::dropDownList(
                    'pageSize',
                    $pageSize,
                    Yii::app()->params['pageSizeOptions'],
                    ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto'])
            )
            . "</div></div>",
    ]);

    ?>
    <!-- To update rows per page via ajax -->
    <script type="text/javascript">
        window.TFA.bind();
    jQuery(function($) {
          jQuery(document).on("change", '#pageSize', function(){
              $.fn.yiiGridView.update('tfa-usermanagement-gridPanel',{ data:{ pageSize: $(this).val() }});
          });
        });
    </script>
<?php endif; ?>
