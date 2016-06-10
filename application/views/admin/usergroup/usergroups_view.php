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
    <h3><?php eT('User groups list'); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->search(),
                    'id' => 'usergroups-grid',
                    'emptyText'=>gT('No user groups found.'),
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
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
                            'type' => 'LongText',
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
                    'ajaxUpdate' => true,
                   ));
            ?>
        </div>
    </div>

</div>

<script type="text/javascript">
jQuery(function($) {
    // To update rows per page via ajax
    $(document).on("change", '#pageSize', function() {
        $.fn.yiiGridView.update('usergroups-grid',{ data:{ pageSize: $(this).val() }});
    });
    //Delete button
    $(document).ready(function() {
        $('a[data-confirm]').click(function() {
            return confirm($(this).attr('data-confirm'));
        });
    });
});
</script>
