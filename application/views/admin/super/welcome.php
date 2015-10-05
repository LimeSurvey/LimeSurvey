<?php
/**
 * The welcome page is the home page
 * TODO : make a recursive function, taking any number of box in the database, calculating how much rows are needed. 
 */
?>

<?php
    // Boxes are defined by user. We still want the default boxes to be translated. 
    gT('Create a new survey');
    gT('List surveys');
    gT('List available surveys');
    gT('Global settings');
    gT('Edit global settings');
    gT('ComfortUpdate');
    gT('Stay safe and up to date');
    gT('Label sets');
    gT('Edit label sets');
    gT('Template editor');
    gT('Edit LimeSurvey templates');
?>

<!-- Welcome view -->
<div class="container-fluid welcome full-page-wrapper">
    
    <!-- Jumbotron -->
    <div class="row">
        <div class="jumbotron" id="welcome-jumbotron">
            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/Limesurvey_logo.png" id="lime-logo" />
            <p><?php eT('This is the LimeSurvey admin interface. From here, you can start to build your survey.')?></p>
        </div>
    </div>

    <!-- Message when first start -->
    <?php if($countSurveyList==0):?>
        <script type="text/javascript">
            $(window).load(function(){
                $('#welcomeModal').modal('show');
            });
        </script>        
                
        <div class="modal fade" id="welcomeModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title"><?php echo sprintf(gT("Welcome to %s!"), 'LimeSurvey'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?php eT("Some piece-of-cake steps to create your very own first survey:"); ?></p>
                        <ol>
                            <li><?php echo sprintf(gT('Create a new survey clicking on the %s icon.'), "<img src='" . Yii::app()->getConfig('adminimageurl') . "add_20.png' name='ShowHelp' title='' alt='" . gT("Add survey") . "'/>"); ?></li>
                            <li><?php eT('Create a new question group inside your survey.'); ?></li>
                            <li><?php eT('Create one or more questions inside the new question group.'); ?></li>
                            <li><?php echo sprintf(gT('Done. Test your survey using the %s icon.'), "<img src='" . Yii::app()->getConfig('adminimageurl') . "do_20.png' name='ShowHelp' title='' alt='" . gT("Test survey") . "'/>"); ?></li>
                        </ol>        
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close');?></button>
                      <a href="<?php echo $this->createUrl("admin/survey/sa/newsurvey") ?>" class="btn btn-primary"><?php eT('Create a new survey');?></a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->        
        
        
        
        
    <?php endif;?>

    <!-- Last visited survey/question -->
    <?php if($showLastSurvey || $showLastQuestion):?>
        <div class="row text-right">
            <div class="col-lg-9 col-sm-9  ">
                <div style="float: right;">
                <?php if($showLastSurvey):?>
                    <span id="last_survey" class="rotateShown">
                    <?php eT("Last visited survey:");?>
                    <a href="<?php echo $surveyUrl;?>" class=""><?php echo $surveyTitle;?></a>
                    </span>
                <?php endif; ?>
                
                <?php if($showLastQuestion):?>
                    <span id="last_question" class="rotateHidden">
                    <?php eT("Last visited question:");?>
                    <a href="<?php echo $last_question_link;?>" class=""><?php echo $last_question_name;?></a>
                    </span>
                <?php endif; ?>
                </div>                
                <br/><br/>
            </div>
        </div>
    <?php endif;?>
    
    
    
    <!-- First row of boxes -->
    <div class="row text-center ">
        
        <!-- First box defined in database -->
        <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                'fromDb'=> true,
                'dbPosition'=>'1',
                'offset' =>'3',
        ));?>

        <!-- 2nd  defined in database -->
        <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                'fromDb'=> true,
                'dbPosition'=>'2',
        ));?>

        <!-- 3rd defined in database -->                
        <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                'fromDb'=> true,
                'dbPosition'=>'3',
        ));?>                

    </div>
    
    <!-- Second row of boxes -->
    <div class="row">
        
        <!-- 4th defined in database -->                
        <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                'fromDb'=> true,
                'dbPosition'=>'4',
                'offset' =>'3',
        ));?>

        <!-- 5th defined in database -->
        <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                'fromDb'=> true,
                'dbPosition'=>'5',
        ));?>
        
        <!-- 6th defined in database -->        
        <?php $this->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                'fromDb'=> true,
                'dbPosition'=>'6',
        ));?>                
    </div>    
</div>

<!-- Notification setting -->
<input type="hidden" id="absolute_notification" />
