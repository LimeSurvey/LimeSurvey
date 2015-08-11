<?php
/**
 * This file display the subscribe view
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 */
?>
<h2 class="maintitle"><?php eT($serverAnswer->title);?></h2>

<?php 
    if( isset($serverAnswer->html) )
        echo $serverAnswer->html;
?>

<div class="updater-background">
    <br/>
    <?php echo html_entity_decode($serverAnswer->message); ?>
    
    <?php
        $url = Yii::app()->createUrl('/admin/update/sa/submitkey');
        echo CHtml::beginForm($url, 'post', array("id"=>"submitKeyForm"));
        echo CHtml::hiddenField('destinationBuild', $_REQUEST['destinationBuild']);
        echo CHtml::textField('keyid', '', array("id"=>"inputKey"));
        echo CHtml::submitButton(gT('Save'), array("class"=>"ui-button ui-widget ui-state-default ui-corner-all", "id"=>"submitKeyButton"));
    ?>  
    
    <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
        <span class="ui-button-text"><?php eT("Cancel"); ?></span>
    </a>
    
    <?php echo CHtml::endForm();?>  
                            
</div>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#submitKeyForm').comfortUpdateNextStep({'step': 0}); 
</script>