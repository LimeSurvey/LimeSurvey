<?php
/**
 * This file render the list of label sets
 * It use the Label Sets model getAllRecords method to build the data provider.
 *
 * @var $this AdminController
 * @var LabelSet $model the LabelSets model
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('viewLabelSets');

?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="col-lg-12">
	<div class="pagetitle h3"><?php eT('Label sets list'); ?></div>

	<div class="row">
        <div class="col-lg-12 content-right">
            <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->search(),

                    // Number of row per page selection
                    'id' => 'labelsets-grid',
                    'emptyText'=>gT('No label sets found.'),
                    'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),

                    'columns' => array(

                        array(
                            'header' => gT('Label set ID'),
                            'name' => 'labelset_id',
                            'value'=>'$data->lid',
                            'htmlOptions' => array('class' => 'col-md-1'),
                        ),

                        array(
                            'header' => gT('Name'),
                            'name' => 'name',
                            'value'=>'$data->label_name',
                            'htmlOptions' => array('class' => 'col-md-2'),
                        ),

                        array(
                            'header' => gT('Languages'),
                            'name' => 'languages',
                            'value'=> '$data->languages',
                            'type' => 'LanguageList',
                            'htmlOptions' => array('class' => 'col-md-6'),
                        ),

                        array(
                            'header'=>'',
                            'name'=>'actions',
                            'type'=>'raw',
                            'value'=>'$data->buttons',
                            'htmlOptions' => array('class' => 'col-md-2 col-xs-1 text-right button-column'),
                        ),

                    ),

                    'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction'),
                    'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/labels/sa/view/lid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                    'ajaxUpdate' => 'labelsets-grid',
                   ));
            ?>
        </div>
    </div>

</div>

<script type="text/javascript">
jQuery(function($) {
    // To update rows per page via ajax
    $(document).on("change", '#pageSize', function() {
        $.fn.yiiGridView.update('labelsets-grid',{ data:{ pageSize: $(this).val() }});
    });
    //Delete button
    $(document).ready(function() {
        $('a[data-confirm]').click(function() {
            return confirm($(this).attr('data-confirm'));
        });
    });
});
</script>
