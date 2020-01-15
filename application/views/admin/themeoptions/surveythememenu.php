<?php
    /**
     * @var AdminController $this
     * @var bool            $canImport
     * @var string          $importErrorMessage
     * @var string          $importModal
     * @var string          $importTemplate
     * @var string          $themeType
     */
?>
<?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
    <div class="row">
        <div class="col-sm-12 content-right">
            <?php if($canImport):?>
                <!-- Import -->
                <a class="btn btn-default" href="" role="button" data-toggle="modal" data-target="#<?php echo $importModal;?>">
                    <span class="icon-import text-success"></span>
                    <?php eT("Import"); ?>
                </a>
            <?php else:?>
                <!-- import disabled -->
                <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php echo $importErrorMessage; ?>" style="display: inline-block">
                    <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                        <span class="icon-import text-success"></span>
                        <?php eT("Import"); ?>
                    </button>
                </span>
            <?php endif;?>
        </div>
    </div>
    <?php $this->renderPartial('themeoptions/import_modal',['importModal' => $importModal, 'importTemplate' => $importTemplate, 'themeType' => $themeType]); ?>
<?php endif;?>