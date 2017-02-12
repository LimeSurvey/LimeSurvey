<?php

/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var CActiveDataProvider $oDataProvider Containing Quota objects*/
/* @var string $editUrl */
/* @var string $deleteUrl */
/* @var array $aQuotaItems */
/* @var integer $totalquotas */
/* @var integer $totalcompleted */



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
                    <?php $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $oDataProvider,
                        'id' => 'quota-grid',
                        'emptyText'=>gT('No quotas'),
                        'enablePagination'=>false,
                        'template' => '{items}',

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
                            array(
                                'name'=>'active',
                                'type'=>'raw',
                                'value'=>function($oQuota){
                                    if($oQuota->active==1){
                                        return CHtml::tag('span',array('class'=>'text-success'),gT("Active"));
                                    }else{
                                        return CHtml::tag('span',array('class'=>'text-danger'),gT("Not active"));
                                    }
                                },
                            ),
                            array(
                                'name'=>'action',
                                'value'=>function($oQuota){
                                    if($oQuota->action==1){
                                        return gT("Terminate survey");
                                    }elseif ($oQuota->action==2){
                                        return gT("Terminate survey with warning");
                                    }
                                    return null;
                                },
                            ),
                            array(
                                'name'=>'completed',
                                'type'=>'raw',
                                'value'=>function($oQuota)use($oSurvey){
                                    $completerCount =getQuotaCompletedCount($oSurvey->sid, $oQuota->id);
                                    $class = ($completerCount <= $oQuota->qlimit ? 'text-warning':null);
                                    $span = CHtml::tag('span',array('class'=>$class),$completerCount);
                                    return $span;
                                },
                                'footer'=>$totalcompleted,
                            ),
                            array(
                                'name'=>'qlimit',
                                'footer'=>$totalquotas,
                            ),
                            array(
                                'header'=>gT("Action"),
                                'value'=>function($oQuota)use($oSurvey,$editUrl,$deleteUrl,$aQuotaItems){
                                    /** @var Quota $oQuota */
                                    $this->renderPartial('/admin/quotas/viewquotas_quota_actions',
                                        array(
                                            'oSurvey'=>$oSurvey,
                                            'oQuota'=>$oQuota,
                                            'editUrl'=>$editUrl,
                                            'deleteUrl'=>$deleteUrl,
                                            'aQuotaItems'=>$aQuotaItems,
                                        ));
                                },
                                'headerHtmlOptions'=>array(
                                    'style'=>'text-align:right;',
                                ),
                                'htmlOptions'=>array(
                                    'align'=>'right',
                                ),
                            ),

                        ),
                        'itemsCssClass' =>'table-striped table-condensed',
                    ));
                    ?>
                </div>
                <?php if (Permission::model()->hasSurveyPermission($oSurvey->getPrimaryKey(), 'quotas','create')):?>
                    <div class="pull-right">
                        <?php echo CHtml::beginForm(array("admin/quotas/sa/newquota/surveyid/{$oSurvey->getPrimaryKey()}"), 'post'); ?>
                        <?php echo CHtml::hiddenField('sid',$oSurvey->getPrimaryKey());?>
                        <?php echo CHtml::hiddenField('action','quotas');?>
                        <?php echo CHtml::hiddenField('subaction','new_quota');?>
                        <?php echo CHtml::submitButton(gT("Add new quota"),array(
                            'name'=>'submit',
                            'class'=>'quota_new btn btn-default',
                        ));?>
                        <?php echo CHtml::endForm();?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>