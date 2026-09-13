<?php if (isset($aImportResults['fatalerror'])):?>
    <div class="jumbotron message-box message-box-error">
            <h2 class="text-danger"><?php eT("Import Label Set") ?></h2>
            <p class="lead text-danger"><?php eT("Error") ?></p>
            <p><?php echo $aImportResults['fatalerror']; ?></p>

            <p>
                <a class="btn btn-lg btn-primary" href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>" role="button">
                    <?php eT("Return to label set administration"); ?>
                </a>
            </p>
    </div>
<?php else:?>
    <div class="jumbotron message-box">
            <h2 class="text-success"><?php eT("Import Label Set") ?></h2>
            <?php if ($aImportResults['labelsets'] > 0): ?>
                <p class="lead text-success"><?php eT("File upload succeeded.") ?></p>
            <?php else: ?>
                <p class="lead text-danger"><?php eT("No new label sets were imported.") ?></p>
            <?php endif; ?>
            <?php if (count($aImportResults['warnings']) > 0): ?>
                <p  class="lead text-danger">
                    <?php eT("Warnings") ?>
                </p>
                <p>
                    <ul class="list-unstyled">
                        <?php foreach ($aImportResults['warnings'] as $warning):?>
                            <li><?php echo $warning ?></li>
                        <?php endforeach;?>
                    </ul>
                </p>
            <?php endif;?>

            <p  class="lead text-success">
                <?php eT("Label set import summary") ?>
            </p>

            <p>
                <ul class="list-unstyled">
                    <li><?php echo gT("Label sets") . ": {$aImportResults['labelsets']}" ?></li>
                    <li><?php echo gT("Labels") . ": {$aImportResults['labels']}" ?></li>
                    <?php if (!empty($aImportResults['duplicates'])): ?>
                        <li><?php echo gT("Duplicate label sets skipped") . ": {$aImportResults['duplicates']}" ?></li>
                    <?php endif; ?>
                </ul>
            </p>

            <p>
                <?php if ($aImportResults['labelsets'] > 0): ?>
                    <strong><?php eT("Import of label set(s) is completed.") ?></strong>
                <?php else: ?>
                    <strong><?php eT("Import completed without adding new label sets.") ?></strong>
                <?php endif; ?>
            </p>

            <p>
                <a class="btn btn-lg btn-primary" href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>" role="button">
                    <?php eT("Return to label set administration"); ?>
                </a>
            </p>
    </div>
<?php endif; ?>
