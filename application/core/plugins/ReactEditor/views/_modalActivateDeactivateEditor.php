<?php

$saveUrl = \Yii::app()->createUrl(
    "plugins/direct/plugin/ReactEditor/function/saveActivateDeactivate",
);

$saveURL = ""; //todo: replace with actual save URL

$cssUrl = \Yii::app()->assetManager->publish(
    dirname(dirname(__DIR__)) . '/ReactEditor/css'
);
\Yii::app()->clientScript->registerCssFile($cssUrl . '/editorModal.css');

?>
<!-- Modal to activate/deactivate the react question editor -->
<div id="activate_editor" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body position-relative p-0">
                <button type="button"
                        class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="z-index: 1050;"></button> <!-- TODO inline style-->
                <input type="hidden" id="saveUrl" name="saveUrl"
                       value="<?= $saveURL ?>">
                <input type="hidden" id="successMsgFeatureOptin"
                       value="<?= gt(
                           'The new editor was successfully activated.'
                       ) ?>">
                <input type="hidden" id="successMsgFeatureOptout"
                       value="<?= gt(
                           'The new editor was successfully deactivated.'
                       ) ?>">
                <div class="card pt-3 pb-5">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <div class="card-body ps-4 pe-4">
                                <h1 class="card-title reg-24 mb-16">
                                    <?= gT('Welcome to the new LimeSurvey') ?>
                                </h1>
                                <p class="card-text reg-14 mb-16"><?= gt(
                                        'With the LimeSurvey Editor, you can create surveys in a 
                                        squeeze of a lime, combining intuitive design with powerful features for a 
                                        faster, smarter survey-building experience.'
                                    ) ?>
                                    <br><br>
                                    <?= gt('Discover what the new editor can do '); ?>
                                    <a class="link-info" href="https://www.limesurvey.org" target="_blank"><?= gt('here'); ?>.</a>
                                </p>
                                <div class="row mb-16">
                                    <label class="label-s mb-1" for='editor-switch-btn'><?php eT("Editor version"); ?></label>
                                    <div class="lime-toggle-btn-group isSecondary">
                                    <?php
                                    Yii::app()->getController()->widget(
                                        'ext.ButtonGroupWidget.ButtonGroupWidget',
                                        [
                                            'name' => 'editor-switch-btn',
                                            'id' => 'editor-switch-btn',
                                            'checkedOption' => true,
                                            'selectOptions' => [
                                                '0' => gT('Classic'),
                                                '1' => gT('New'),
                                            ],
                                        ]
                                    ); ?>
                                    </div>
                                        <br>
                                </div>
                                <div class="hint-text-box p-3">
                                    <p class="hint-text med-14-c mb-1">
                                        <?= gt('Good to know') ?>
                                    </p>
                                    <p class="hint-text reg-12">
                                        <?= gT(
                                                "You can switch between Classic and New Editor anytime from your account settings. We recommend trying the new version, now out of beta and we’d love to hear your feedback!"
                                            ) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <img src="/application/core/plugins/ReactEditor/images//new_editor_image_small.png"
                                 class="img-fluid editor-preview"
                                 alt="Editor preview">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>