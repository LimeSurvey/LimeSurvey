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

<div class="updater-background">
    <p>
    <?php eT('The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.');?></p><p>
    <?php
        $aopen  = '<a href="https://www.limesurvey.org/en/your-account/your-details" target="_blank">';
        $aclose = '</a>';
    ?>
    <?php echo sprintf(gT("You can get free trial update key from %syour account on the limesurvey.org website%s."),$aopen, $aclose); ?>
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