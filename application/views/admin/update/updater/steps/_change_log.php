<?php
/**
 * This view displays the Step 1 : pre-installation checks.
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 *
 * @var obj $serverAnswer the object returned by the server
 * @var int $destinationBuild the destination build
 */
?>



<h3 class="maintitle"><?php eT('Change log'); ?></h3>


<div class="row">
    <div class="col-12">
<?php if ($html_from_server != "") :?>
    <div>
        <?php echo $html_from_server;?>
    </div>
<?php endif;?>

<?php
$changelog = "";
$currentVersion = Yii::app()->getConfig("versionnumber") . " Build " . Yii::app()->getConfig("buildnumber");
foreach (array_reverse($changelogs->changelogentries) as $changelogentry) {
    if (trim($changelogentry->changelog != '')) {
          $tempfromversion = $changelogentry->versionnumber;
          $tempfrombuild = $changelogentry->build;

          $changelog .= "Changes in {$changelogentry->versionnumber} Build {$changelogentry->build} from {$currentVersion} --- Legend: + New feature, # Updated feature, - Bug fix\n";
          $changelog .= $changelogentry->changelog."\n";
          $currentVersion = "{$changelogentry->versionnumber} Build {$changelogentry->build}";
    }
}

?>

<textarea class="updater-changelog form-control" readonly="readonly" rows="20">
<?php
echo $changelog;
?>
</textarea>

    </div>
</div>
<div class="row">
    <div class="col-12 mt-2">

        <?php
            $formUrl = Yii::app()->getController()->createUrl("admin/update/sa/filesystem/");
            echo CHtml::beginForm($formUrl, 'post', array("id" => "launchFileSystemForm"));
            echo CHtml::hiddenField('destinationBuild', $destinationBuild);
            echo CHtml::hiddenField('access_token', $access_token);
        ?>

        <a class="btn btn-cancel me-1"
           href="<?= Yii::app()->createUrl("admin/update"); ?>"
           role="button"
           aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>


    <?php
        echo CHtml::submitButton(gT('Continue', 'unescaped'), array('id' => 'step2launch', "class" => "btn btn-primary ajax_button launch_update"));
        echo CHtml::endForm();
    ?>

    </div>
</div>
<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
$('#launchFileSystemForm').comfortUpdateNextStep({'step': 2});
</script>
