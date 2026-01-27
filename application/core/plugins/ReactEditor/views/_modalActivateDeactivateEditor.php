<?php
/**
$saveUrl = \Yii::app()->createUrl(
    "plugins/direct/plugin/LimeSurveyProfessional/function/saveFeatureOptInOut"
);**/

$saveURL =""; //todo: replace with actual save URL

$cssUrl = \Yii::app()->assetManager->publish(
    dirname(dirname(__DIR__)) . '/css'
);
\Yii::app()->clientScript->registerCssFile($cssUrl . '/featurePreview.css');
?>
<!-- Modal to activate/deactivate the react question editor -->
<div id="activate_editor" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="saveUrl" name="saveUrl" value="<?= $saveUrl ?>">
                <input type="hidden" id="successMsgFeatureOptin"
                       value="<?= gt('The new editor was successfully activated.') ?>">
                <input type="hidden" id="successMsgFeatureOptout"
                       value="<?= gt('The new editor was successfully deactivated.') ?>">

                <div class="card card-beta-feature">
                    <div class="card-body p-4 pb-4">
                        <div class="row">
                            <h6 class="card-title pb-1">
                                <?= gT('Welcome to the new LimeSurvey') ?>
                            </h6>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="card-text">
                                    <?= gt('With the LimeSurvey Editor, you can create surveys in a 
                                    squeeze of a lime, combining intuitive design with powerful features for a 
                                    faster, smarter survey-building experience.') ?>
                                </p>
                                <p class="card-text">
                                    <?= gt('Discover what the new editor can do '); ?>
                                    <a href="https://www.limesurvey.org/de" target="_blank"><?= gt('here'); ?></a>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <?php
                                Yii::app()->getController()->widget(
                                    'ext.ButtonGroupWidget.ButtonGroupWidget',
                                    [
                                        'name' => $feature['feature_name'],
                                        'id' => $feature['id'],
                                        'checkedOption' => $feature['isOptedIn'],
                                        'selectOptions' => [
                                            '0' => gT('Classic'),
                                            '1' => gT('New'),
                                        ],
                                        'htmlOptions' => [
                                            'class' => 'feature-opt-in-btn', //todo css for the switch in purpel
                                        ]
                                    ]
                                ); ?>
                            </div>
                            <div class="col-md-4">
                                <p id="good-to-know">
                                    <?= gt('Good to know') ?>
                                    <br>
                                    <?= sprintf(gT("You can switch between Classic and New Editor at any time. We recommend trying the new version, now out of beta—and we’d love to hear your feedback! "))?>
                                </p>
                            </div>
                            <div class="col-md-4 pe-0">
                                <img src="/application/core/plugins/ReactEditor/images//new_editor_image_small.png"
                                     alt="Editor preview">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
