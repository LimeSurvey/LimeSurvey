<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var CActiveDataProvider $oDataProvider Containing Quota item objects*/
/* @var array $aQuotaItems */

$oDataProvider=new CArrayDataProvider($aQuotaItems[$oQuota->id]);

$this->widget('bootstrap.widgets.TbGridView', array(
    'dataProvider' => $oDataProvider,
    'id' => 'quota-members-grid',
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
            'header'=>getAddAnswerButton($oSurvey,$oQuota),
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
                'style'=>'text-align:right;',
            ),
            'htmlOptions'=>array(
                'align'=>'right',
            ),

        ),

    ),
    'itemsCssClass' =>'table-striped',
));

?>
