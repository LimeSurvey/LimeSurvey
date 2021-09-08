<?php
/**
 * This file render the list of label sets
 *
 * @var $this AdminController
 * @var LabelSet $model the LabelSets model
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('viewLabelSets');

?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="col-lg-12">

	<div class="row">
        <div class="col-lg-12 content-right">
            <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->search(),

                    // Number of row per page selection
                    'id'               => 'labelsets-grid',
                    'emptyText'        => gT('No label sets found.'),
                    'template'         => "{items}\n<div id='labelsetsListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'htmlOptions'      => ['class' => 'table-responsive grid-view-ls'],
                    'selectionChanged' => "function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/labels/sa/view/lid') . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                    'ajaxUpdate'       => 'labelsets-grid',
                    'summaryText'      => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
                    'columns' => array(

                        array(
                            'header'=>gT('Action'),
                            'name'=>'actions',
                            'type'=>'raw',
                            'value'=>'$data->buttons',
                            'htmlOptions' => array('class' => 'text-center button-column'),
                        ),

                        array(
                            'header' => gT('Label set ID'),
                            'name' => 'labelset_id',
                            'value'=>'$data->lid',
                            'htmlOptions' => array('class' => ''),
                        ),

                        array(
                            'header' => gT('Name'),
                            'name' => 'name',
                            'value'=>'$data->label_name',
                            'htmlOptions' => array('class' => ''),
                        ),

                        array(
                            'header' => gT('Languages'),
                            'name' => 'languages',
                            'value'=> '$data->languages',
                            'type' => 'LanguageList',
                            'htmlOptions' => array('class' => ''),
                        ),

                    ),

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
