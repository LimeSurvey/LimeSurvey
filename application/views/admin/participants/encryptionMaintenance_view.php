<?php
/**
 * @var AdminController $this
 */

?>
<div class="pjax-content">
    <?php if (!$currentDuplicateFinderInvalidParticipantId): ?>
        <div class="card mb-1">
            <div class="card-header"><h2 class="h4"><?= gT("Re-encrypt participant data") ?></h2></div>
            <div class="card-body">
                <?php echo Chtml::form(['admin/participants/sa/reencryptParticipantData']) ?>
                <?php if ($currentReencryptLastParticipantId): ?>
                    <p><?= gT("Re-encrypt all participant data using the current encryption settings. You can resume the operation from the last participant processed during the current session or restart the operation from the beginning.") ?></p>
                    <?= 
                    Chtml::htmlButton(
                        sprintf(gT("Continue from last position, still %s to encrypt"), $currentReencryptStillToProcess),
                        [ 'name' => 'rencrypt', 'value' => 'continue', 'type' => 'submit', 'class'=> 'btn btn-primary']
                    ) ?>
                    <?= Chtml::htmlButton(
                        gT("Start from beginning"),
                        [ 'name' => 'rencrypt', 'value' => 'reset', 'type' => 'submit', 'class'=> 'btn btn-secondary']
                    ) ?>
                <?php else: ?>
                    <p><?= gT("Re-encrypt all participant data using the current encryption settings. You may need to click the button several times to complete the operation.") ?></p>
                    <?= Chtml::htmlButton(
                        gT("Start"),
                        [ 'name' => 'rencrypt', 'value' => 'reset', 'type' => 'submit', 'class'=> 'btn btn-primary']
                    ) ?>
                <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!$currentReencryptLastParticipantId && $duplicateFinderInvalidCount): ?>
        <div class="card">
            <div class="card-header"><h2 class="h4"><?= gT("Recalculate participant duplicate indexes") ?></h2></div>
            <div class="card-body">
                <?php echo Chtml::form(['admin/participants/sa/recalculateDuplicateFinder']) ?>
                <?php if ($currentDuplicateFinderInvalidParticipantId): ?>
                    <p><?= gT("Recalculates the duplicate index for all participants using the current duplicate detection settings. You can resume the operation from the last participant processed during the current session or restart the operation from the beginning.") ?></p>
                    <?= 
                    Chtml::htmlButton(
                        sprintf(gT("Continue from last position, still %s to fix"), $currentDuplicateFinderInvalidStillToProcess),
                        [ 'name' => 'rencrypt', 'value' => 'continue', 'type' => 'submit', 'type' => 'submit', 'class'=> 'btn btn-primary']
                    ) ?>
                    <?= Chtml::htmlButton(
                        gT("Start from the beginning"),
                        [ 'name' => 'rencrypt', 'value' => 'reset', 'type' => 'submit', 'class'=> 'btn btn-secondary']
                    ) ?>
                <?php else: ?>
                    <p><?= gT("Recalculates the duplicate index for all participants using the current duplicate detection settings. You may need to click the button several times to complete the operation.") ?></p>
                    <?= Chtml::htmlButton(
                        gT("Start"),
                        [ 'name' => 'rencrypt', 'value' => 'reset', 'type' => 'submit', 'class'=> 'btn btn-primary']
                    ) ?>
                <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

