<?php
/**
* This file render the list of surveys
* It use the Survey model search method to build the data provider.
*
* @var $model  Survey
*/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('listSurveys');

?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="ls-space row list-surveys">
    <ul class="nav nav-tabs" id="surveysystem" role="tablist">
        <li class="active"><a href="#surveys" aria-controls="surveys" role="tab" data-toggle="tab"><?php eT('Survey list'); ?></a></li>
        <li><a href="#surveygroups" aria-controls="surveygroups" role="tab" data-toggle="tab"><?php eT('Survey groups'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="surveys" class="tab-pane active">
            <!-- Survey List widget -->
            <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                        'pageSize' => Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                        'model' => $model
                ));
            ?>
        </div>

        <div id="surveygroups" class="tab-pane">
            <div class="pagetitle h3 ls-space margin top-25"><?php eT('Survey groups'); ?></div>
            <div class="row">
                <div class="col-sm-12 content-right">
                    <?php
                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $groupModel->search(),
                        'columns' => $groupModel->columns,
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' ',
                        'itemsCssClass' =>'table-striped',
                        'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view'),
                        'selectionChanged'=>"function(id){window.location='" . Yii::app()->urlManager->createUrl("admin/surveysgroups/sa/update/id" ) . '/' . "' + $.fn.yiiGridView.getSelection(id.split(',', 1));}",
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#surveysystem a').on('shown.bs.tab', function () {
        var tabId = $(this).attr('href');
        $('.tab-dependent-button:not([data-tab="' + tabId + '"])').hide();
        $('.tab-dependent-button[data-tab="' + tabId + '"]').show();
    });
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#surveysystem').find('a[href='+window.location.hash+']').trigger('click');
        }
    })
</script>
