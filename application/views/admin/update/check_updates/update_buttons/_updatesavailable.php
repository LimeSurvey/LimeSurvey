<?php
/**
 * This view display the buttons "use ComfortUpdate".
 * It is injected by the javascript inside the li#udapteButtonsContainer, in the _checkButton view.
 * @var obj updateInfos the update informations provided by the update server
 * @var obj $clang : the translate object, now moved to global function TODO : remove it
 */
?>
<ul>
<li>
<label><?php eT('The following LimeSurvey updates are available:');?></label><br>
<table class='table'>
<thead>
<tr><th>
<?php eT('Version'); ?></th><th><?php eT('Actions'); ?></th></tr>
</thead>
<?php
// First we check if the server provided a specific HTML message
if(isset($updateInfos->html))
{
    if($updateInfos->html != "")
        echo '<tr><td>'.$updateInfos->html.'</tr></td>';

    // And we unset this html message for the loop on update versions don't crush on it
    unset($updateInfos->html);
}

?>


<?php foreach ($updateInfos as $aUpdateVersion):?>
    <?php $aUpdateVersion = (array) $aUpdateVersion;?>
    <tr>
        <td>
            <?php
                // display infos about the update. e.g : "2.05+ (150508) (stable)"
                echo $aUpdateVersion['versionnumber'];?> (<?php echo $aUpdateVersion['build'];?>) <?php if ($aUpdateVersion['branch']!='master') eT('(unstable)'); else eT('(stable)');
            ?>
        </td>
        <td>
            <?php $url = Yii::app()->createUrl("admin/update/sa/getwelcome"); ?>
            <!-- The form launching an update process. First step is the welcome message. The form is not submitted, but catch by the javascript inserted in the end of this file -->
            <?php echo CHtml::beginForm($url, 'post', array('class'=>'launchUpdateForm')); ?>
                <?php echo CHtml::hiddenField('destinationBuild' , $aUpdateVersion['build']); ?>

                <!-- the button launching the update -->
                <?php echo CHtml::submitButton(gT("Use ComfortUpdate"), array('class'=>"ajax_button launch_update ui-button ui-widget ui-state-default ui-corner-all",)); ?>


                <?php if ($aUpdateVersion['branch']!='master'): ?>
                    <input type='button' class="ajax_button ui-button ui-widget ui-state-default ui-corner-all" onclick="window.open('http://www.limesurvey.org/en/unstable-release/viewcategory/26-unstable-releases', '_blank')" value='<?php eT("Download"); ?>' />
                <?php else: ?>
                    <input type='button' class="ajax_button ui-button ui-widget ui-state-default ui-corner-all" onclick="window.open('http://www.limesurvey.org/en/stable-release', '_blank')" value='<?php eT("Download"); ?>' />
                <?php endif; ?>

            <?php echo CHtml::endForm(); ?>
        </td>
    </tr>
<?php endforeach; ?>
</table>
</li>
</ul>
<!-- this javascript code manage the step changing. It will catch the form submission, then load ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('.launchUpdateForm').comfortUpdateNextStep({'step': 0});
</script>
