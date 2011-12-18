<?php

echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'4').'</div><div class="updater-background"><br />';

if (!isset( $_SESSION['updateinfo']))
{
	echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
	if ($updateinfo['error']==1)
    {
        echo $clang->gT('Your update key is invalid and was removed. ').'<br />';
    }
    else
	    echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
}

if ($new_files)
{
	echo $clang->gT('New files were successfully installed.').'<br />';
}
else
{
	echo $clang->gT('There was a problem downloading the update file. Please try to restart the update process.').'<br />';
}

if (!$downloaderror)
{
	echo sprintf($clang->gT('Buildnumber was successfully updated to %s.'),$_SESSION['updateinfo']['toversion']).'<br />';
	echo $clang->gT('Please check any problems above - update was done.').'<br />';
}

echo "<p><button onclick=\"window.open('".site_url("admin/globalsettings")."', '_top')\" >".$clang->gT('Back to main menu')."</button></p>";
echo '</div>';

?>
