<?php
/* @var $this AdminController */
/* @var Survey $oSurvey */
/* @var Quota $oQuota */
/* @var CActiveDataProvider $oDataProvider Containing Quota item objects*/

?>

<?php
$surveyGrid = $this->widget('bootstrap.widgets.TbGridView', array(
    'dataProvider' => $oDataProvider,
    'id' => 'quota-grid',
    'emptyText'=>gT('No quotas'),

    'columns' => array(

        array(
            'name'=>'completed',
            'type'=>'raw',
            'value'=>function($oQuota)use($oSurvey){
                return getQuotaCompletedCount($oSurvey->sid, $oQuota->id);
            },
        ),
        'qlimit',

    ),
    'itemsCssClass' =>'table-striped',
));
?>
