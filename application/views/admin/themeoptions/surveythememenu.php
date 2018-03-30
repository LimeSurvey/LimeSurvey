<?php
/**
 * @var bool $canImport
 * @var string $importErrorMessage
 */
?>
<div class="row">
    <div class="col-sm-12 content-right">
        <?php if($canImport):?>
            <!-- Import -->
            <a class="btn btn-default" href="" role="button" data-toggle="modal" data-target="#importModal">
                <span class="icon-import text-success"></span>
                <?php eT("Import"); ?>
            </a>
        <?php else:?>
            <!-- import disabled -->
            <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $importErrorMessage; ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom">
                <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                    <span class="icon-import text-success"></span>
                    <?php eT("Import"); ?>
                </button>
            </span>
        <?php endif;?>
    </div>
</div>
