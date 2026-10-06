<?php

/** @var bool $activated  */
/** @var bool $hasPathUrlFormat  */
/** @var string $warningHeader  */
/** @var string $warningMessage  */
/** @var array<int, array{image: string, title: string, description: string}> $slides */

$saveUrl = \Yii::app()->createUrl(
    "plugins/direct/plugin/ReactEditor/function/saveActivateDeactivate",
);

$colClassLeft = $hasPathUrlFormat ? 'col-md-5' : 'col-md-6';
$colClassRight = $hasPathUrlFormat ? 'col-md-7' : 'col-md-6 pt-3';
$slidesJson = htmlspecialchars(json_encode($slides), ENT_QUOTES, 'UTF-8');
?>
<!-- Modal to activate/deactivate the react question editor -->
<div id="activate_editor" class="modal fade" role="dialog"
     data-slides="<?= $slidesJson ?>">
    <div class="modal-dialog modal-xl">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body position-relative p-0">
                <button type="button"
                        class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="z-index: 1050;"></button> <!-- TODO inline style-->
                <input type="hidden" id="saveUrl" name="saveUrl"
                       value="<?= $saveUrl ?>">
                <input type="hidden" id="successMsgFeatureOptin"
                       value="<?= gT('The new editor was enabled successfully. You can always switch the editor version from your account settings.') ?>">
                <input type="hidden" id="successMsgFeatureOptout"
                       value="<?= gT('The new editor was successfully deactivated.') ?>">
                <input type="hidden" id="errorOnSave"
                       value="<?= gT('An error occurred while saving.') ?>">
                <div class="card">
                    <div class="row g-0">
                        <div class="<?= $colClassLeft ?>">
                            <div class="card-body ps-4 pe-4">
                                <div class="card-body-top">
                                    <h1 class="card-title reg-24 mb-16">
                                    <?= gT('Welcome to the new LimeSurvey') ?>
                                    </h1>
                                    <!-- Slide description (injected by JS) -->
                                    <div class="editor-slider-description-wrap mb-16">
                                        <p class="editor-slider-description reg-14"></p>
                                    </div>
                                </div>
                                <div class="card-body-bottom">
                                    <!-- Auto-open mode: single CTA button (shown only when opened automatically) -->
                                    <div class="editor-auto-open-section d-none row mb-16">
                                        <div class="d-grid gap-2">
                                            <button type="button"
                                                    id="switch-new-editor-btn"
                                                    class="btn btn-info"
                                                    <?= !$hasPathUrlFormat ? 'disabled' : '' ?>>
                                                <?= gT('Switch to new editor') ?>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Manual mode: toggle between classic/new (shown when opened manually) -->
                                    <div class="editor-manual-section row mb-16">
                                        <label class="label-s mb-1" for='editor-switch-btn'><?php eT("Editor version"); ?></label>
                                        <div class="lime-toggle-btn-group isSecondary">
                                        <?php
                                        App()->getController()->widget(
                                            'ext.ButtonGroupWidget.ButtonGroupWidget',
                                            [
                                                'name'          => 'editor-switch-btn',
                                                'id'            => 'editor-switch-btn',
                                                'checkedOption' => $activated,
                                                'selectOptions' => [
                                                    '0' => gT('Classic'),
                                                    '1' => gT('New'),
                                                ],
                                                'htmlOptions' => ['disabled' => !$hasPathUrlFormat],
                                            ],
                                        ); ?>
                                        </div>
                                            <br>
                                    </div>
                                    <!-- Custom per-slide info (injected by JS) -->
                                    <div class="editor-info-custom d-none"></div>
                                    <?php if ($hasPathUrlFormat) : ?>
                                    <!-- Default info shown for slides without their own info section -->
                                    <div class="editor-info-default hint-text-box p-3">
                                        <p class="hint-text med-14-c mb-1">
                                            <?= gT('Good to know...') ?>
                                        </p>
                                        <p class="hint-text reg-12">
                                            <?= gT("You can switch between classic and new editor anytime from your account settings. We recommend trying the new version -  it’s now out of beta, and we’d love to hear your feedback!") ?>
                                        </p>
                                    </div>
                                    <?php else :
                                        App()->getController()->widget('ext.AlertWidget.AlertWidget', [
                                            'header' => $warningHeader,
                                            'text'   => $warningMessage,
                                            'type'   => 'warning',
                                        ]);
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="<?= $colClassRight ?> editor-slider-column">
                            <div class="editor-slider">
                                <div class="editor-slide-title-wrap">
                                    <h1 class="editor-slider-title reg-24"></h1>
                                </div>
                                <div class="editor-slider-image-wrap">
                                    <img class="editor-slider-image img-fluid" src="" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="editor-slider-footer">
                        <div class="<?= $colClassRight ?> editor-slider-nav">
                            <button type="button"
                                    class="editor-slider-arrow editor-slider-arrow--prev"
                                    aria-label="<?= gT('Previous slide') ?>">
                                <span class="ri-arrow-left-line"></span>
                            </button>
                            <div class="editor-slider-dots"
                                 role="tablist"
                                 aria-label="<?= gT('Slides') ?>"></div>
                            <button type="button"
                                    class="editor-slider-arrow editor-slider-arrow--next"
                                    aria-label="<?= gT('Next slide') ?>">
                                <span class="ri-arrow-right-line"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
