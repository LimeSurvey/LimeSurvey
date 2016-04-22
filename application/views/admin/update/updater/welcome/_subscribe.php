<?php
/**
 * This file display the subscribe view
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 */
?>
<h2 class="maintitle"><?php eT("Subscribe to ComfortUpdate!");?></h2>

<?php
    if( isset($serverAnswer->html) )
        echo $serverAnswer->html;
?>


<?php if(isset($serverAnswer->alert_message) && $serverAnswer->alert_message=="subscribe_lts"):?>
    <div id="update-alert" class="alert alert-info alert-dismissible" role="alert" style="background-color: #fff; border: 1px solid #800051; color: #800051; margin-top:  1em;">
        <button aria-disabled="false" role="button" type="button" class="close ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" data-dismiss="alert" aria-label="Close"><span class="ui-button-text"><span aria-hidden="true">×</span></span></button>
        <?php printf(gT('To use comfortUpdate for LTS version, %s you need to subscribe to a Premium Package %s. Free keys are not accepted.'), '<a href="https://www.limesurvey.org/services">', '</a>' );?>


    </div>
<?php endif;?>


<div class="updater-background">
    <p>
    <?php eT('The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.');?></p><p>
    <?php
        $aopen  = '<a href="https://www.limesurvey.org/en/your-account/your-details" target="_blank">';
        $aclose = '</a>';
    ?>
    <?php echo sprintf(gT("You can get a free trial update key from %syour account on the limesurvey.org website%s."),$aopen, $aclose); ?>
    <?php
        $aopen  = '<a href="https://www.limesurvey.org/en/cb-registration/registers">';
        $aclose = '</a>';
    ?><br>
    <?php echo sprintf(gT("If you don't have an account on limesurvey.org, please %sregister first%s."),$aopen, $aclose);?></p>
    <?php
        $url = Yii::app()->createUrl('/admin/update/sa/submitkey');
        echo CHtml::beginForm($url, 'post', array("id"=>"submitKeyForm"));
        echo CHtml::hiddenField('destinationBuild', $_REQUEST['destinationBuild']);
        echo CHtml::label(gT('Enter your update key:'),'inputKey');
        echo CHtml::textField('keyid', '', array("id"=>"inputKey")).' ';
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
