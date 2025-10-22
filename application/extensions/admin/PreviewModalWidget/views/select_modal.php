<?php
/**
 * View for a selector modal with preview capabilities and a grouped structure
 * Used by "Export" modal on survey overview page.
 */
?>
<?php //The hidden input ?>
<input id="<?= $this->widgetsJsName ?>" name="<?= $this->widgetsJsName ?>" value="<?= $this->value ?>" type="hidden"/>
<?php //The modal ?>
<div class="modal fade previewModalWidget" data-bs-backdrop="false" tabindex="-1" id="selector__<?= $this->widgetsJsName ?>-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" role="heading" aria-level="2"><?= gT($this->modalTitle) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-4 ls-ba">
                        <div class="ls-space padding all-0">
                            <div class="list-group ls-space margin all-0">
                                <?php foreach ($this->itemsArray as $sItemKey => $aItemContent) { ?>
                                    <a href="#"
                                       class="list-group-item selector__Item--select-<?= $this->widgetsJsName ?> <?= @$aItemContent['htmlclasses'] ?>"
                                       data-selector="<?= !empty($aItemContent['class']) ? $aItemContent['class'] : $sItemKey ?>"
                                       data-key="<?= $sItemKey ?>"
                                       data-item-value='<?= json_encode([
                                           "key"       => $sItemKey,
                                           "title"     => htmlentities((string) $aItemContent['title']),
                                           "itemArray" => $aItemContent
                                       ]); ?>'
                                        <?= @$aItemContent['extraAttributes'] ?>
                                    >
                                        <?= $aItemContent['title'] ?>
                                        <?php if (YII_DEBUG) : ?>
                                            <em class="small"><?= gT($this->debugKeyCheck) ?> <?= $sItemKey ?></em>
                                        <?php endif; ?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="row">
                            <div class="col-12">
                                <h3>
                                    <p id="selector__<?= $this->widgetsJsName ?>-currentSelected"><?= $this->currentSelected ?></p>
                                </h3>
                            </div>
                        </div>
                        <div class="row" id="selector__<?= $this->widgetsJsName ?>-detailPage">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                    <?= gT($this->closeButton) ?>
                </button>
                <button type="button" id="selector__select-this-<?= $this->widgetsJsName ?>" class="btn btn-primary">
                    <?= gT($this->selectButton) ?>
                </button>
            </div>
        </div>
    </div>
</div>
