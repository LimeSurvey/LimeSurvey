<?php

    /**
    * Renders a list of items as widget cards. Each item can be a survey box/card or a link box/card.
    *
    * @param array $items An array of items where each item contains information about either a survey or a link.
    *
    * @example
    * $items = [
    *     [
    *         'type' => 0, // Survey type
    *         'link' => 'survey-link',
    *         'survey' => (object)[
    *             'defaultlanguage' => (object)['surveyls_title' => 'Survey Title'],
    *             'creationdate' => '2023-01-01',
    *             'getRunning' => 'Running Status',
    *             'countFullAnswers' => 5,
    *             'active' => 'Y',
    *             'groupsCount' => 3,
    *             'getQuestionsCount' => 10,
    *             'sid' => 12345,
    *             'getButtons' => '<button>Button</button>'
    *         ]
    *     ],
    *     [
    *         'type' => 2, // Link type
    *         'link' => 'external-link',
    *         'colored' => true,
    *         'icon' => 'icon-class',
    *         'text' => 'Link Text',
    *         'external' => true
    *     ]
    * ];
    */

?>
<?php if (!empty($items)) : ?>
    <?php foreach ($items as $item) : ?>
        <?php if ($item['type'] == BoxesWidget::TYPE_PRODUCT) : ?>
            <div class="box-widget-card align-middle d-inline-block"
                 data-url="<?php echo $item['link'] ?>">
                <div class="box-widget-card-body">
                    <div class="box-widget-card-header">
                        <div class="box-widget-card-title">
                            <?php echo viewHelper::filterScript(gT($item['survey']->defaultlanguage->surveyls_title)); ?>
                        </div>
                    </div>
                    <div class="box-widget-card-text">
                        <div>
                            <?= $item['survey']->creationdate ?>
                        </div>
                        <div>
                            <?= $item['survey']->getRunning() ?>
                        </div>
                    </div>
                    <div class="box-widget-card-footer">
                        <div class="box-widget-card-footer-items">
                            <div>
                                <?php echo $item['survey']->countFullAnswers == 0 ? 'No' : $item['survey']->countFullAnswers ?> responses
                            </div>
                            <div class="icons">
                                <?php if (
                                    ($item['survey']->active === "N")
                                    && ($item['survey']->groupsCount > 0)
                                    && ($item['survey']->getQuestionsCount() > 0)
    ) :
                                    ?>
                                    <a href="<?= App()->createUrl("/surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/" . $item['survey']->sid) ?? '#' ?>"
                                       class="active"
                                       data-bs-toggle="tooltip"
                                       data-bs-original-title="<?= gT('Activate') ?>"
                                    >
                                        <i class="ri-check-line"></i>
                                    </a>
                                <?php elseif ($item['survey']->active !== "Y") : ?>
                                    <a href="<?= App()->createUrl("/surveyAdministration/view?iSurveyID=" . $item['survey']->sid) ?? '#' ?>"
                                       class="active"
                                       data-bs-toggle="tooltip"
                                       data-bs-original-title="<?= gT('Edit survey') ?>"
                                    >
                                        <i class="ri-edit-line"></i>
                                    </a>
                                <?php elseif ($item['survey']->active === "Y") : ?>
                                    <a href="<?= App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/" . $item['survey']->sid) ?? '#' ?>"
                                       class="active"
                                       data-bs-toggle="tooltip"
                                       data-bs-original-title="<?= gT('Statistics') ?>"
                                    >
                                        <i class="ri-line-chart-line"></i>
                                    </a>
                                <?php endif; ?>

                                <?php echo $item['survey']->getButtons(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($item['type'] == BoxesWidget::TYPE_LINK) : ?>
            <div class="box-widget-card card-link m-2 align-middle d-inline-block <?php echo $item['colored']? 'card-link-highlight' : ''; ?>"
                 data-url="<?php echo $item['link'] ?>"
                <?php if ($item['external']) :
                    ?> data-target="_blank" <?php
                endif; ?>
            >
                <div class="box-widget-card-body">
                    <i class="<?php echo $item['icon'] ?>"></i>
                    <?php echo $item['text'] ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else : ?>
    <div class="survey-actionbar col-12">
        <p>
            <?php echo gT('No surveys found.'); ?>
        </p>
    </div>
<?php endif; ?>
