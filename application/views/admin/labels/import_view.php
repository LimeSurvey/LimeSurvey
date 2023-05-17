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
            <p class="lead"><?php eT("File upload succeeded.") ?></p>
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
                </ul>
            </p>

            <p>
                <strong><?php eT("Import of label set(s) is completed.") ?></strong>
            </p>

            <p>
                <a class="btn btn-lg btn-primary" href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>" role="button">
                    <?php eT("Return to label set administration"); ?>
                </a>
            </p>
    </div>
<?php endif; ?>
