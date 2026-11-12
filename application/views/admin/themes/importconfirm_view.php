<?php
/**
 * Asks for confirmation before an imported survey theme overwrites an existing one.
 *
 * @var string $overwriteThemeName Name of the existing theme
 * @var string $overwriteToken Token binding this page to the uploaded theme file
 * @var bool $isInstalled Whether the existing theme is installed
 */
?>
<div class="row">
    <div class="col-md-11 offset-md-1 content-right">
        <div class="jumbotron message-box message-box-warning">
            <h2><?php eT("Overwrite existing theme?") ?></h2>
            <p class="lead">
                <?php echo CHtml::encode(sprintf(gT("Template '%s' does already exist.", 'unescaped'), $overwriteThemeName)); ?>
            </p>
            <p>
                <?php eT("If you continue, the files of the existing theme are replaced by the files of the uploaded theme. This can't be undone."); ?>
            </p>
            <p>
                <?php if ($isInstalled) : ?>
                    <?php eT("Files that are not part of the uploaded theme (for example uploaded images) and the theme options you have set are kept."); ?>
                <?php else : ?>
                    <?php eT("Files that are not part of the uploaded theme (for example uploaded images) are kept."); ?>
                <?php endif; ?>
            </p>
            <?php echo CHtml::form(['admin/themes/sa/upload'], 'post', ['id' => 'canceloverwritetheme', 'class' => 'd-inline']); ?>
                <input type="hidden" name="action" value="templateoverwritecancel"/>
                <input type="hidden" name="overwritetoken" value="<?php echo CHtml::encode($overwriteToken); ?>"/>
                <button type="submit" class="btn btn-outline-secondary btn-lg" id="button-cancel-overwrite-theme">
                    <?php eT("Cancel"); ?>
                </button>
            <?php echo CHtml::endForm(); ?>
            <?php echo CHtml::form(['admin/themes/sa/upload'], 'post', ['id' => 'overwritetheme', 'class' => 'd-inline']); ?>
                <input type="hidden" name="action" value="templateoverwrite"/>
                <input type="hidden" name="overwritetoken" value="<?php echo CHtml::encode($overwriteToken); ?>"/>
                <button type="submit" class="btn btn-danger btn-lg" id="button-overwrite-theme">
                    <?php eT("Overwrite theme"); ?>
                </button>
            <?php echo CHtml::endForm(); ?>
        </div>
    </div>
</div>
