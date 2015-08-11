<?php 
/**
 * This view displays the Step 1 : pre-installation checks. 
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 * 
 * @var obj $serverAnswer the object returned by the server
 * @var int $destinationBuild the destination build 
 */

?>



<h2 class="maintitle"><?php eT('Change log'); ?></h4>

<?php if($html_from_server!=""):?>
    <div>
        <?php echo $html_from_server;?>
    </div>
<?php endif;?>

<?php
    $changelog = "";
    if($changelogs->changingBranch)
    {
        $changelog.= gt("Note:               Because you are updating from a stable to an unstable version or vice versa \n                           a change log might not be available or not complete.\n\n");
    }
     
   foreach  ($changelogs->changelogentries as $changelogentry)
   {
        if (trim($changelogentry->changelog !=''))
        {
                    
            $tempfromversion=$changelogentry->versionnumber;
            $tempfrombuild=$changelogentry->build;
        
            $changelog.="Changes from $tempfromversion Build $tempfrombuild to {$changelogentry->versionnumber} Build {$changelogentry->build} --- Legend: + New feature, # Updated feature, - Bug fix\n";
            $changelog.=$changelogentry->changelog;
        }
   }

?>
        
<textarea class="updater-changelog" readonly="readonly" style="background-color: #FFF">
<?php
echo $changelog;
?>
</textarea>

    <?php 
        $formUrl = Yii::app()->getController()->createUrl("admin/update/sa/fileSystem/");
        echo CHtml::beginForm($formUrl, 'post', array("id"=>"launchFileSystemForm"));
        echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
        echo CHtml::hiddenField('access_token' , $access_token);
    ?>
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php eT("Cancel"); ?></span>
        </a>         
    
    <?php 
        echo CHtml::submitButton(sprintf(gT('Continue')), array('id'=>'step2launch', "class"=>"ui-button ui-widget ui-state-default ui-corner-all")); 
        echo CHtml::endForm(); 
    ?>      

    
<!-- this javascript code manage the step changing. It will catch the form submission, then load ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
$('#launchFileSystemForm').comfortUpdateNextStep({'step': 2});  
</script>