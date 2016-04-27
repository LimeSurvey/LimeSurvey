<?php
/**
 * This view display the result of the update
 * @var int $destinationBuild the destination build
 */
opcache_reset();
?>

<h2 class="maintitle"><?php eT('Update complete!'); ?></h2>
<div class="updater-background">
    <?php
        echo sprintf(gT('Buildnumber was successfully updated to %s.'),Yii::app()->session['updateinfo']['toversion']).'<br />';
        eT('The update is now complete!');
    ?>
    <br/>
    <?php
        eT("if needed the database will be updated as a last step.");
    ?>
    <br />
<?php  eT('As a last step you should clear your browser cache now.'); ?>
  <br />

  <a id="backToMainMenu" class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/authentication/sa/logout"); ?>" role="button" aria-disabled="false">
      <?php eT('Click this button to log out.'); ?>
  </a>
</div>

<script>
    $('#backToMainMenu').comfortUpdateNextStep({'step': 5});
</script>
