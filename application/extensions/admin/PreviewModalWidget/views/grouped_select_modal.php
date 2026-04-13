<?php
/**
 * View for a selector modal with preview capabilities and a grouped structure
 */
?>

<?php //The modal ?>
<input id="<?= $this->widgetsJsName ?>" name="<?= $this->widgetsJsName ?>" value="<?= $this->value ?>" type="hidden"/>
<div class="modal fade previewModalWidget" tabindex="-1" role="dialog" id="selector__<?= $this->widgetsJsName ?>-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= gT($this->modalTitle) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-4 ls-ba">
                        <div class="accordion" id="accordion_<?= $this->widgetsJsName ?>" role="tablist"
                             aria-multiselectable="true">
                            <?php foreach ($this->groupStructureArray as $sGroupTitle => $aGroupArray) { ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" role="tab" id="heading_<?= $sGroupTitle ?>">
                                        <button
                                            role="button"
                                            type="button"
                                            class="accordion-button collapsed"
                                            data-bs-toggle="collapse"
                                            data-bs-parent="#accordion_<?= $this->widgetsJsName ?>"
                                            href="#collapsible_<?= $sGroupTitle ?>"
                                            aria-expanded="true"
                                            aria-controls="collapse-question"
                                        >
                                            <?= $aGroupArray[$this->groupTitleKey] ?>
                                        </button>
                                    </h2>

                                    <div
                                        id="collapsible_<?= $sGroupTitle ?>"
                                        class="accordion-collapse collapse"
                                        role="tabpanel"
                                        aria-labelledby="<?= $sGroupTitle ?>"
                                        data-bs-parent="#accordion_<?= $this->widgetsJsName ?>"
                                    >
                                        <div class="accordion-body ls-space padding all-0">
                                            <div class="list-group ls-space margin all-0">
                                                <?php foreach ($aGroupArray[$this->groupItemsKey] as $aItemContent) { ?>
                                                    <a
                                                        href="#"
                                                        class="list-group-item selector__Item--select-<?= $this->widgetsJsName ?> <?= @$aItemContent['htmlclasses'] ?>"
                                                        data-selector="<?= !empty($aItemContent['class']) ? $aItemContent['class'] : $aItemContent['type'] ?>"
                                                        data-key="<?= $aItemContent['type'] ?>"
                                                        data-item-value='<?= json_encode([
                                                            "key"       => $aItemContent['type'],
                                                            "title"     => htmlentities((string) $aItemContent['title']),
                                                            "itemArray" => $aItemContent
                                                        ]); ?>'
                                                        <?= @$aItemContent['extraAttributes'] ?>
                                                    >
                                                        <?= $aItemContent['title'] ?>
                                                        <?php if (YII_DEBUG) {
                                                            ?>
                                                            <em class="small"><?= gT($this->debugKeyCheck) ?> <?= $aItemContent['type'] ?></em>
                                                            <?php
                                                        } ?>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
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
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= gT($this->closeButton) ?>
                </button>
                <button type="button" id="selector__select-this-<?= $this->widgetsJsName ?>" class="btn btn-primary">
                    <?= gT($this->selectButton) ?>
                </button>
            </div>
        </div>
    </div>
</div>
