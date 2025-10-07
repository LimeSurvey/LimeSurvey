<?php
    include_once './application/extensions/admin/BoxesWidget/BoxesWidget.php';
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
                        <div class="box-widget-card-date">
                            <?= $item['survey']->creationdate ?>
                        </div>
                        <div class="box-widget-card-status">
                            <?= $item['survey']->getRunning() ?>
                        </div>
                    </div>
                    <div class="box-widget-card-footer">
                        <div class="box-widget-card-footer-items">
                            <div class="box-widget-card-footer-response">
                                <?php
                                if ($item['survey']->countFullAnswers == 0) {
                                    $responsesInfo = gT('No responses');
                                } else {
                                    $responsesInfo = sprintf(
                                        gT('%d responses'),
                                        $item['survey']->countFullAnswers
                                    );
                                }
                                echo $responsesInfo;
                                ?>
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
                                        <i class="ri-bar-chart-2-line"></i>
                                    </a>
                                <?php endif; ?>

                                <?php echo $item['survey']->getButtons(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($item['type'] == BoxesWidget::TYPE_LINK) : ?>
            <div class="box-widget-card card-link align-middle d-inline-block <?= $item['colored'] ? 'card-link-highlight' : ''; ?>"
                 data-url="<?= $item['link'] ?>" <?= $item['external'] ? 'target="_blank"' : ''?>>
                <div class="box-widget-card-body">
                    <i class="<?= $item['icon'] ?>"></i>
                    <?= $item['text'] ?>
                </div>
            </div>
        <?php elseif ($item['type'] == BoxesWidget::TYPE_PLACEHOLDER) : ?>
            <div class="box-widget-card card-link card-placeholder d-inline-block">
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
