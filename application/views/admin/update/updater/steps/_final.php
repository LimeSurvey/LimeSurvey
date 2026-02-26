<?php
/**
 * This view display the result of the update
 * @var int $destinationBuild the destination build
 */
?>

<h3 class="maintitle"><?php eT('Update complete!'); ?></h3>
<div class="updater-background">
    <div class="row">
        <div class="col-12">
            <?php
            $message = gT('The update is now complete!') . '</br>' .
                gT("If necessary the database will be updated in a final step.") . '</br>' .
                gT('However, it is important that you clear your browser cache now. After that please click the button below.');
            $this->widget('ext.AlertWidget.AlertWidget', [
                'header' => sprintf(gT('Buildnumber was successfully updated to %s.'), $destinationBuild),
                'text' => $message,
                'type' => 'success',
            ]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mt-2">
          <a id="backToMainMenu"
             class="btn btn-primary"
             href="<?= Yii::app()->createUrl("admin/authentication/sa/logout"); ?>"
             role="button"
             aria-disabled="false">
              <?php eT('Finish'); ?>
          </a>
        </div>
    </div>
</div>

<script>
    $('#backToMainMenu').comfortUpdateNextStep({'step': 5});
</script>
