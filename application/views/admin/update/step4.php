<?php

echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'4').'</div><div class="updater-background"><br />';

if (!isset( Yii::app()->session['updateinfo']))
{
	$clang->eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	if ($updateinfo['error']==1)
    {
        $clang->eT('Your update key is invalid and was removed. ').'<br />';
    }
    else
	    $clang->eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
}

if ($new_files)
{
	$clang->eT('New files were successfully installed.').'<br />';
}
else
{
	$clang->eT('There was a problem downloading the update file. Please try to restart the update process.').'<br />';
}

if (!$downloaderror)
{
	echo sprintf($clang->gT('Buildnumber was successfully updated to %s.'),Yii::app()->session['updateinfo']['toversion']).'<br />';
	$clang->eT('Please check any problems above - update was done.').'<br />';
}

echo "<p><button onclick=\"window.open('".site_url("admin/globalsettings")."', '_top')\" >".$clang->gT('Back to main menu')."</button></p>";
echo '</div>';

?>
