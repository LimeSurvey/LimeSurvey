<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('usersIndex');

?>

<?php if (!Permission::model()->hasGlobalPermission('users', 'read')) :?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><?=gT("We are sorry but you don't have permissions to do this.")?></h2>
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
<?php App()->getController()->renderPartial('/admin/usermanagement/partial/_menubar'); ?>
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
                'summaryText'   => "<div class='row' style='text-align:left; color:#000'>"
                ."<div class='col-xs-6 content-left'>".$massiveAction."</div>"
                ."<div class='col-xs-6'>"
                .gT('Displaying {start}-{end} of {count} result(s).').' '
                    . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            App()->params['pageSizeOptions'],
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
            //show tooltip for gridview icons
            $('body').tooltip({selector: '[data-toggle="tooltip"]'});
        </script>
</div>
<div id='UserManagement-action-modal' class="modal fade UserManagement--selector--modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
