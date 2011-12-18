<?php

echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'3').'</div><div class="updater-background">';
echo '<h3>'.$clang->gT('Creating DB & file backup').'</h3>';

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


echo $clang->gT('Creating file backup... ').'<br />';

echo "<span class='successtitle'>".$clang->gT('File backup created:').' '.htmlspecialchars($tempdir.DIRECTORY_SEPARATOR.'files-'.$basefilename.'.zip').'</span><br /><br />';

if ($databasetype=='mysql' || $databasetype=='mysqli')
{
	echo $clang->gT('Creating database backup... ').'<br />';
	echo "<span class='successtitle'>".$clang->gT('DB backup created:')." ".htmlspecialchars($tempdir.DIRECTORY_SEPARATOR.'db-'.$basefilename.'.sql').'</span><br /><br />';
}
else
{
	echo "<span class='warningtitle'>".$clang->gT('No DB backup created:').'<br />'.$clang->gT('Database backup functionality is currently not available for your database type. Before proceeding please backup your database using a backup tool!').'</span><br /><br />';
}

echo $clang->gT('Please check any problems above and then proceed to the final step.');
echo "<p><button onclick=\"window.open('".site_url("admin/update/step4/")."', '_top')\" ";
echo ">".sprintf($clang->gT('Proceed to step %s'),'4')."</button></p>";
echo '</div>';

?>
