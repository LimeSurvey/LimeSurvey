<?php
/**
* This file render the list of surveys
* It use the Survey model search method to build the data provider.
*
* @var $model  obj    the QuestionGroup model
*/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('listSurveys');

?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="ls-space margin left-15 right-15 row list-surveys">
    <ul class="nav nav-tabs" id="surveysystem" role="tablist">
        <li class="active"><a href="#surveys"><?php eT('Survey list'); ?></a></li>
        <li><a href="#surveygroups"><?php eT('Survey groups'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="surveys" class="tab-pane active">
            <?php if(Permission::model()->hasGlobalPermission('surveys','create')):?>
                <div class="col-12">
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/survey/sa/newsurvey"); ?>" role="button">
                        <span class="icon-add text-success"></span>
                        <?php eT("Create a new survey");?>
                    </a>
                </div>
            <?php endif;?>
            <div class="pagetitle h3 ls-space margin top-25"><?php eT('Survey list'); ?></div>
            <!-- Survey List widget -->
            <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                        'pageSize' => Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                        'model' => $model
                ));
            ?>
        </div>

        <div id="surveygroups" class="tab-pane">
            <?php if(Permission::model()->hasGlobalPermission('surveys','create')):?>
                <div class="col-12">
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/surveysgroups/sa/create"); ?>" role="button">
                        <span class="icon-add text-success"></span>
                        <?php eT("Create a new survey group");?>
                    </a>
                </div>
            <?php endif;?>
            <div class="pagetitle h3 ls-space margin top-25"><?php eT('Survey groups'); ?></div>
            <div class="row">
                <div class="col-sm-12 content-right">
                    <?php
                    $this->widget('bootstrap.widgets.TbGridView', array(
                        'dataProvider' => $groupModel->search(),
                        'columns' => $groupModel->columns,
                        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#surveysystem a').click(function (e) {
        window.location.hash = $(this).attr('href');
        e.preventDefault();
        $(this).tab('show');
    });
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#surveysystem').find('a[href='+window.location.hash+']').trigger('click');
        }
    })
</script>
