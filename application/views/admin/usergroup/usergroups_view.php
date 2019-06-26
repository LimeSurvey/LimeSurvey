<?php
/**
 * This file render the list of user groups
 * It use the Label Sets model search method to build the data provider.
 *
 * @var $model  obj    the UserGroup model
 */
?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="col-lg-12">
    <div class="pagetitle h3"><?php eT('User group list'); ?></div>
    
    <div class="h4"><?php 
            if (!Permission::model()->hasGlobalPermission('superadmin','read'))
            {
                eT('My user groups');
            }
        ?>
    </div>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->searchMine(TRUE),
                    'id' => 'usergroups-grid-mine',
                    'emptyText'=>gT('No user groups found.'),
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

                    'columns' => array(

                        array(
                            'header' => gT('User group ID'),
                            'name' => 'usergroup_id',
                            'value'=>'$data->ugid',
                            'htmlOptions' => array('class' => 'col-md-1'),
                        ),

                        array(
                            'header' => gT('Name'),
                            'name' => 'name',
                            'value'=>'$data->name',
                            'htmlOptions' => array('class' => 'col-md-2'),
                        ),

                        array(
                            'header' => gT('Description'),
                            'name' => 'description',
                            'value'=> '$data->description',
                            'htmlOptions' => array('class' => 'col-md-5'),
                        ),

                        array(
                            'header' => gT('Owner'),
                            'name' => 'owner',
                            'value'=> '$data->owner->users_name',
                            'htmlOptions' => array('class' => 'col-md-1'),
                        ),

                        array(
                            'header' => gT('Members'),
                            'name' => 'members',
                            'value'=> '$data->countUsers',
                            'htmlOptions' => array('class' => 'col-md-1'),
                        ),
                        
                        array(
                            'header'=>'',
                            'name'=>'actions',
                            'type'=>'raw',
                            'value'=>'$data->buttons',
                            'htmlOptions' => array('class' => 'col-md-2 col-xs-1 text-right'),
                        ),                            

                    ),

                    'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction'),
                    'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/usergroups/sa/view/ugid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                    'ajaxUpdate' => 'usergroups-grid-mine',
                   ));
            ?>
        </div>
    </div>
    
    <div class="h4"><?php
        if (!Permission::model()->hasGlobalPermission('superadmin','read'))
        {
            eT('Groups to which I belong');
        }
        ?>
    </div>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            if (!Permission::model()->hasGlobalPermission('superadmin','read'))
            {
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->searchMine(FALSE),
                    'id' => 'usergroups-grid-belong-to',
                    'emptyText'=>gT('No user groups found.'),
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
                    'columns' => $model->columns,
                    'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction'),
                    'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/usergroups/sa/view/ugid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                    'ajaxUpdate' => 'usergroups-grid-belong-to',
                   ));
            }
            ?>
        </div>
    </div>

</div>

<div class="modal fade" tabindex="-1" id="delete-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?=gT("Delete this user group")?></h4>
            </div>
            <div class="modal-body">
                <?=CHtml::form(array("admin/usergroups/sa/delete"), 'post', array('class'=>'','id'=>'delete-modal-form','name'=>'delete-modal-form'))?>
                    <p><?=gT('Are you sure you want to delete this user group?')?></p>
                    <input type="hidden" name="ugid" id="delete-ugid" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=gT('Cancel')?></button>
                <button type="button" class="btn btn-danger" id="confirm-deletion"><?=gT('Yes')?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($) {
    // To update rows per page via ajax
    $(document).on("change", '#pageSize', function() {
        $.fn.yiiGridView.update('usergroups-grid-mine',{ data:{ pageSize: $(this).val() }});
        $.fn.yiiGridView.update('usergroups-grid-belong-to',{ data:{ pageSize: $(this).val() }});
    });
    //Delete button
    $(document).ready(function() {
        $('.action__delete-group').on('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            $('#delete-modal').modal('show'); 
            
            $('#delete-ugid').val($(this).data('ugid'));

            $('#confirm-deletion').on('click', function() {
                $('#delete-modal-form').submit();
            });
        });
    });
});
</script>
