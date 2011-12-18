<?php
echo '<div class="header ui-widget-header">'.$clang->gT('Welcome to the ComfortUpdate').'</div><div class="updater-background"><br />';
echo $clang->gT('The LimeSurvey ComfortUpdate is an easy procedure to quickly update to the latest version of LimeSurvey.').'<br />';
echo $clang->gT('The following steps will be done by this update:').'<br /><ul>';
echo '<li>'.$clang->gT('Your LimeSurvey installation is checked if the update can be run successfully.').'</li>';
echo '<li>'.$clang->gT('Your DB and any changed files will be backed up.').'</li>';
echo '<li>'.$clang->gT('New files will be downloaded and installed.').'</li>';
echo '<li>'.$clang->gT('If necessary the database will be updated.').'</li></ul>';
echo '<h3>'.$clang->gT('Checking basic requirements...').'</h3>';
if ($updatekey==''){
    echo $clang->gT('You need an update key to run the comfort update. During the beta test of this update feature the key "LIMESURVEYUPDATE" can be used.');
    echo "<br /><form id='keyupdate' method='post' action='".Yii::app()->createUrl("admin/update/index/keyupdate")."'><label for='updatekey'>".$clang->gT('Please enter a valid update-key:').'</label>';
    echo '<input id="updatekey" name="updatekey" type="text" value="LIMESURVEYUPDATE" /> <input type="submit" value="'.$clang->gT('Save update key').'" /></form>';
}
else
{
    echo "<ul><li class='successtitle'>".$clang->gT('Update key: Valid')."</li>";

    if (!is_writable($tempdir))
    {
        echo  "<li class='errortitle'>".sprintf($clang->gT("Tempdir %s is not writable"),$tempdir)."<li>";
        $error=true;
    }
    if (!is_writable(APPPATH.'config/version.php'))
    {
        echo  "<li class='errortitle'>".sprintf($clang->gT("Version file is not writable (%s). Please set according file permissions."),APPPATH.'config/version.php')."</li>";
        $error=true;
    }
    echo '</ul><h3>'.$clang->gT('Change log').'</h3>';

    if($httperror=="") {
    	echo '<textarea class="updater-changelog" readonly="readonly">'.htmlspecialchars($changelog['changelog']).'</textarea>';
    }
    else {
    	print($httperror);
    }

    if ($error)
    {
        echo '<br /><br />'.$clang->gT('When checking your installation we found one or more problems. Please check for any error messages above and fix these before you can proceed.');
        echo "<p><button onclick=\"window.open('".Yii::app()->createUrl("admin/update/index/")."', '_top')\"";
        echo ">".$clang->gT('Check again')."</button></p>";
    }
    else
    {
        echo '<br /><br />'.$clang->gT('Everything looks alright. Please proceed to the next step.');
        echo "<p><button onclick=\"window.open('".Yii::app()->createUrl("admin/update/step2/")."', '_top')\"";
        if ($updatekey==''){    echo "disabled='disabled'"; }
        echo ">".sprintf($clang->gT('Proceed to step %s'),'2')."</button></p>";
    }
    echo '</div>';    
}
?>
