<?php
/**
 * This view display the result of the update
 * @var int $destinationBuild the destination build
 */
?>

<h2 class="maintitle"><?php eT('Update complete!'); ?></h2>
<div class="updater-background">
    <?php
        echo sprintf(gT('Buildnumber was successfully updated to %s.'),$destinationBuild).'<br />';
        eT('The update is now complete!');
    ?>
    <br/>
    <?php
        eT("If necessary the database will be updated in a final step.");
    ?>
    <br /><?php
        eT('However it is very important that you clear your browser cache now. After that please click the button below.'); ?>
  <br />

  <a id="backToMainMenu" class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/authentication/sa/logout"); ?>" role="button" aria-disabled="false">
      <?php eT('Finish'); ?>
  </a>
</div>

<script>
    $('#backToMainMenu').comfortUpdateNextStep({'step': 5});
</script>
