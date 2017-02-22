<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var CActiveDataProvider $oDataProvider Containing Quota item objects*/
/* @var array $aQuotaItems */
?>
<?php if (!empty($aQuotaItems) ):?>



            <div class="panel panel-<?php echo ($oQuota->active==1 ? 'primary' : 'default') ?>">
                <div class="panel-heading">
                    <?php eT("Quota members");?>
        <?php echo CHtml::beginForm(array("admin/quotas/sa/new_answer/surveyid/{$oSurvey->getPrimaryKey()}"), 'post');?>
        <?php echo CHtml::hiddenField('sid',$oSurvey->getPrimaryKey());?>
        <?php echo CHtml::hiddenField('action','quotas');?>
        <?php echo CHtml::hiddenField('quota_id',$oQuota->getPrimaryKey());?>
        <?php echo CHtml::hiddenField('subaction','new_answer');?>
        <?php echo CHtml::submitButton(gT("Add answer"),array(
            'name'=>'submit',
            'class'=>'quota_new btn btn-default btn-xs',
        ));?>
        <?php echo CHtml::endForm();?>

        </span>
                </div>

                <div class="panel-body" style="margin: 3px;padding: 3px;">
                    <?php
                    if (!empty($aQuotaItems) && isset($aQuotaItems[$oQuota->id]) ){

                        $oDataProvider=new CArrayDataProvider($aQuotaItems[$oQuota->id]);
                        $this->widget('bootstrap.widgets.TbGridView', array(
                            'dataProvider' => $oDataProvider,
                            'id' => 'quota-members-grid',
                            'enablePagination'=>false,
                            'template' => '{items}',

                            'columns' => array(

                                array(
                                    'header'=>gT("Questions"),
                                    'name'=>'question_title',
                                ),
                                array(
                                    'header'=>gT("Answers"),
                                    'name'=>'answer_title',
                                ),
                                array(
                                    'type'=>'raw',
                                    'value'=>function($data)use($oQuota,$oSurvey){
                                        $this->renderPartial('/admin/quotas/viewquotas_quota_members_actions',
                                            array(
                                                'oSurvey'=>$oSurvey,
                                                'oQuota'=>$oQuota,
                                                'oQuotaMember' =>$data['oQuotaMember'],
                                            ));
                                    },
                                    'headerHtmlOptions'=>array(
                                        'style'=>'text-align:right;padding:3px;',
                                    ),
                                    'htmlOptions'=>array(
                                        'align'=>'right',
                                        'style'=>'text-align:right;padding:3px;margin:0;',
                                    ),

                                ),

                            ),
                            'itemsCssClass' =>'table-striped table-condensed',
                        ));
                    }    ?>
                </div>
            </div>


<?php endif;