<?php

echo '<div class="header ui-widget-header">'.sprintf($clang->gT('ComfortUpdate step %s'),'2').'</div><div class="updater-background"><br />';
if(!($error=="")) {
	print( $error );
}

if (isset($updateinfo['error']))
{
    $clang->eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
}

if (!isset($updateinfo['files']))
{
    echo "<div class='messagebox ui-corner-all'>
        <div class='warningheader'>".$clang->gT('Update server busy')."</div>
        <p>".$clang->gT('The update server is currently busy. This usually happens when the update files for a new version are being prepared.')."<br /><br />
           ".$clang->gT('Please be patient and try again in about 10 minutes.')."</p></div>
        <p><button onclick=\"window.open('".Yii::app()->getController()->createUrl("admin/globalsettings")."', '_top')\">".sprintf($clang->gT('Back to global settings'),'4')."</button></p>";
}
else
{
	echo '<h3>'.$clang->gT('Checking existing LimeSurvey files...').'</h3>';
	if (count($readonlyfiles)>0)
    { ?>
        
        <span class="warningtitle"><?php $clang->eT('Warning: The following files/directories need to be updated but their permissions are set to read-only.'); ?><br />
        <?php $clang->eT('You must set according write permissions on these filese before you can proceed. If you are unsure what to do please contact your system administrator for advice.'); ?><br />
        </span><ul>
        <?php
        foreach ($readonlyfiles as $readonlyfile)
        {?>
            <li><?php echo htmlspecialchars($readonlyfile); ?></li>
        <?php
        }?>
        </ul>
      <?php  
    }
    if (count($existingfiles)>0)
    {
        $clang->eT('The following files would be added by the update but already exist. This is very unusual and may be co-incidental.');?><br />
        <?php  $clang->eT('We recommend that these files should be replaced by the update procedure.');?><br />
        <ul>
        <?php
        sort($existingfiles);
        foreach ($existingfiles as $existingfile)
        {
            echo '<li>'.htmlspecialchars($existingfile['file']).'</li>';
        }
        echo '</ul><br>';
    }

    if (count($modifiedfiles)>0)
    {
        $clang->eT('The following files will be modified or deleted but were already modified by someone else.');?><br>
        <?php
        $clang->eT('We recommend that these files should be replaced by the update procedure.');?><br>
        <ul> 
        <?php
        sort($modifiedfiles);
        foreach ($modifiedfiles as $modifiedfile)
        {
            echo '<li>'.htmlspecialchars($modifiedfile['file']).'</li>';
        }
        echo '</ul><br>';
    }

    if (count($readonlyfiles)>0)
    {
        echo '<br />'.$clang->gT('When checking your file permissions we found one or more problems. Please check for any error messages above and fix these before you can proceed.');
        echo "<p><button onclick=\"window.open('".Yii::app()->getController()->createUrl("admin/update/sa/step2/")."', '_top')\"";
        echo ">".$clang->gT('Check again')."</button></p>";
    }
    else
    {
        $clang->eT('Please check any problems above and then proceed to the next step.').'<br />';
        echo "<p><button onclick=\"window.open('".Yii::app()->getController()->createUrl("admin/update/sa/step3/")."', '_top')\" ";
        echo ">".sprintf($clang->gT('Proceed to step %s'),'3')."</button></p>";

    }
}
echo "</div>";
?>
