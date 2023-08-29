<?php
/**
 * This view displays the Step 1 : pre-installation checks.
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 *
 * @var array $_data_ the data array
 * @var string $versionInfoPath partial path with information about this specific version target
 * @var obj $serverAnswer the object returned by the server
 * @var int $destinationBuild the destination build
 * @var string $html_from_server additional html that can be set by the server
 * @var string $currentVersionNumber the current version number
 * @var array $changelogs all the changelogs between the current version and the destination version
 */

?>

<?php if (isset($destinationMajorVersion)) : ?>

    <h3 class="maintitle"><?= sprintf(gT('Welcome to LimeSurvey %s!'), $destinationMajorVersion) ?></h3>
<?php else : ?>
    <h3 class="maintitle"><?= gT('Change log') ?></h3>
<?php endif; ?>
<div class="row">
    <div class="col-lg-12">

        <?php if (!empty($html_from_server)) : ?>
                <?= $html_from_server ?>
        <?php endif; ?>

        <?php if (isset($destinationMajorVersion)) : ?>
            <h4><?= gT('Change log') ?>:</h4>
        <?php endif; ?>
        <?php
        $changelog = "";
        $currentVersion = $currentVersionNumber . " Build " . App()->getConfig("buildnumber");
        foreach (array_reverse($changelogs->changelogentries) as $changelogentry) {
            if (trim($changelogentry->changelog) !== '') {
                $tempfromversion = $changelogentry->versionnumber;
                $tempfrombuild = $changelogentry->build;

                $changelog .= "Changes in {$changelogentry->versionnumber} Build {$changelogentry->build} from {$currentVersion} --- Legend: + New feature, # Updated feature, - Bug fix\n";
                $changelog .= $changelogentry->changelog . "\n";
                $currentVersion = "{$changelogentry->versionnumber} Build {$changelogentry->build}";
            }
        }
        ?>
        <textarea class="updater-changelog form-control" readonly="readonly" style="background-color: #FFF" rows="20"><?= $changelog ?></textarea>
    </div>
</div>
<div class="row">
    <div class="col-lg-12" style="margin-top : 1em">

        <?php
        $formUrl = Yii::app()->getController()->createUrl("admin/update/sa/filesystem/");
        echo CHtml::beginForm($formUrl, 'post', ["id" => "launchFileSystemForm"]);
        echo CHtml::hiddenField('destinationBuild', $destinationBuild);
        echo CHtml::hiddenField('access_token', $access_token);
        ?>

        <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>


        <?php
        echo CHtml::submitButton(gT('Continue', 'unescaped'), ['id' => 'step2launch', "class" => "btn btn-default ajax_button launch_update"]);
        echo CHtml::endForm();
        ?>

    </div>
</div>
<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchFileSystemForm').comfortUpdateNextStep({'step': 2});
</script>
