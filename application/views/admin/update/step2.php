<?php

echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'2').'</div><div class="updater-background"><br />';
if(!($error=="")) {
	print( $error );
}

if (isset($updateinfo['error']))
{
    echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';

    if ($updateinfo['error']==1)
    {
        setGlobalSetting('updatekey','');
        echo $clang->gT('Your update key is invalid and was removed. ').'<br />';
    }
    else
    echo $clang->gT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
}

if (!isset($updateinfo['files']))
{
    echo "<div class='messagebox ui-corner-all'>
        <div class='warningheader'>".$clang->gT('Update server busy')."</div>
        <p>".$clang->gT('The update server is currently busy. This usually happens when the update files for a new version are being prepared.')."<br /><br />
           ".$clang->gT('Please be patient and try again in about 10 minutes.')."</p></div>
        <p><button onclick=\"window.open('".Yii::app()->createUrl("admin/globalsettings")."', '_top')\">".sprintf($clang->gT('Back to global settings'),'4')."</button></p>";
}
else
{
	echo '<h3>'.$clang->gT('Checking existing LimeSurvey files...').'</h3>';
	if (count($readonlyfiles)>0)
    {
        
        $readonlyfiles=array_unique($readonlyfiles);
        sort($readonlyfiles);
        foreach ($readonlyfiles as $readonlyfile)
        {
            echo '<li>'.htmlspecialchars($readonlyfile).'</li>';
        }
        echo '</ul>';
    }
    if (count($existingfiles)>0)
    {
        echo $clang->gT('The following files would be added by the update but already exist. This is very unusual and may be co-incidental.').'<br />';
        echo $clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';
        echo '<ul>';
        sort($existingfiles);
        foreach ($existingfiles as $existingfile)
        {
            echo '<li>'.htmlspecialchars($existingfile['file']).'</li>';
        }
        echo '</ul>';
    }

    if (count($modifiedfiles)>0)
    {
        echo $clang->gT('The following files will be modified or deleted but were already modified by someone else.').'<br />';
        echo $clang->gT('We recommend that these files should be replaced by the update procedure.').'<br />';
        echo '<ul>';
        sort($modifiedfiles);
        foreach ($modifiedfiles as $modifiedfile)
        {
            echo '<li>'.htmlspecialchars($modifiedfile['file']).'</li>';
        }
        echo '</ul>';
    }

    if (count($readonlyfiles)>0)
    {
        echo '<br />'.$clang->gT('When checking your file permissions we found one or more problems. Please check for any error messages above and fix these before you can proceed.');
        echo "<p><button onclick=\"window.open('".Yii::app()->createUrl("admin/update/step2/")."', '_top')\"";
        echo ">".$clang->gT('Check again')."</button></p>";
    }
    else
    {
        echo $clang->gT('Please check any problems above and then proceed to the next step.').'<br />';
        echo "<p><button onclick=\"window.open('".Yii::app()->createUrl("admin/update/step3/")."', '_top')\" ";
        echo ">".sprintf($clang->gT('Proceed to step %s'),'3')."</button></p>";

    }
}
echo "</div>";
?>
