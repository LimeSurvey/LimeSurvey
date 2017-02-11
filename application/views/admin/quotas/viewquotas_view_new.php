<?php

/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota[] $aQuotas */
/* @var CActiveDataProvider $oDataProvider */
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=> gT("Survey quotas"))); ?>
            <h3>
                <?php eT("Survey quotas");?>
            </h3>

            <?php if( isset($sShowError) ):?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong><?php eT("Quota could not be added!", 'js'); ?></strong><br/> <?php eT("It is missing a quota message for the following languages:", 'js'); ?><br/><?php echo $sShowError; ?>
                </div>
            <?php endif; ?>


            <!-- Grid -->
            <div class="row">
                <div class="col-sm-12 content-right">
                    <?php
                    $surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $oDataProvider,

                        // Number of row per page selection
                        'id' => 'survey-grid',
                        'emptyText'=>gT('No quotas.'),

                        'columns' => array(

                            array(
                                'id'=>'id',
                                'class'=>'CCheckBoxColumn',
                                'selectableRows' => '100',
                            ),
                            array(
                                'name'=>'name',
                                'value'=>'$data->name',
                            ),

                        ),
                        'itemsCssClass' =>'table-striped',
                        //'htmlOptions'=>array('style'=>'cursor: pointer;'),
                        'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view'),
                        //'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl('admin/survey/sa/view/surveyid' ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                        'ajaxUpdate' => true,
                        'afterAjaxUpdate' => 'doToolTip',
                    ));
                    ?>
                </div>
            </div>


        </div>
    </div>
</div>