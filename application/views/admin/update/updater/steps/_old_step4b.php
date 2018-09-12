<?php
/**
 * This is the last view from the old updater. It is used only for updates from old uptader. 
 */
?>
<div class="header ui-widget-header">
<?php echo sprintf(gT('ComfortUpdate step %s'),'4');?>

</div><div class="updater-background"><br />
<?php
if (!isset( Yii::app()->session['updateinfo']))
{
	eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	if ($updateinfo['error']==1)
    {
        eT('Your update key is invalid and was removed. ').'<br />';
    }
    else
	    eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
}

if ($new_files)
{
	eT('New files were successfully installed.');
}
else
{
	eT('There was a problem downloading the update file. Please try to restart the update process.');
}
?>
<br>
<?php
if (!$downloaderror)
{
	echo sprintf(gT('Buildnumber was successfully updated to %s.'),Yii::app()->session['updateinfo']['toversion']).'<br />';
    eT('The update is now complete!'); ?> <br /> <?php
    eT('As a last step you should clear your browser cache now.');?> <br /> <?php
}

echo "<p><button onclick=\"window.open('".Yii::app()->getController()->createUrl("admin/globalsettings")."', '_top')\" >".gT('Back to main menu'); ?>
</button></p>
</div>
