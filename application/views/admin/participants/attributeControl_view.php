<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsAttributeControl');

?>
<div id="pjax-content">
    <div class="col-xs-12 ">
        <div class="pagetitle row h3">
            <div class="col-xs-8 ">
                <?php eT("Attribute management"); ?>
            </div>
            <div class="col-xs-4 text-right">
                <button class="btn btn-default" id="addParticipantAttributeName">
                    <i class="fa fa-plus-circle text-success"></i> 
                    &nbsp;
                    <?php eT("Add new attribute"); ?>
                </button>
            </div>
        </div>

        <div class="row">
            <div class="container-fluid">
            <div class="row">

            </div>
            <div class="row">
                <?php
                $this->widget('bootstrap.widgets.TbGridView', array(
                    'id' => 'list_attributes',
                    'itemsCssClass' => 'table table-striped items',
                    'dataProvider' => $model->search(),
                    'columns' => $model->columns,
                    'filter'=>$model,
                    'emptyText'=>gT('No attributes found.'),
                    'htmlOptions' => array('class'=> 'table-responsive'),
                    'rowHtmlOptionsExpression' => '["data-attribute_id" => $data->attribute_id]',
                    'itemsCssClass' => 'table table-responsive table-striped',
                    'afterAjaxUpdate' => 'LS.CPDB.bindButtons',
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
                    'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSizeAttributes',
                                    $pageSizeAttributes,
                                    Yii::app()->params['pageSizeOptions'],
                                    array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                                ),
                        ));
                    ?>
                </div>
        </div>

                <div id="pager">

                </div>
            </div>
        </div>
        <span id="locator" data-location="attributes">&nbsp;</span>
    </div>
</div>
