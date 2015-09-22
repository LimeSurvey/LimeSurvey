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

<div class="container-fluid welcome full-page-wrapper">
    
    <!-- Jumbotron -->
	<div class="row">
		<div class="jumbotron" id="welcome-jumbotron">
			<img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/Limesurvey_logo.png" id="lime-logo" />
			<p><?php eT('This is the LimeSurvey admin interface. From here, you can start to build your survey.')?></p>
		</div>
	</div>

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

<input type="hidden" id="absolute_notification" />
