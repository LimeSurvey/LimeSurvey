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
