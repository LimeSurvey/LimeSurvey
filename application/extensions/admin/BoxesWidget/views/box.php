<?php foreach ($items as $item) : ?>
    <div class="col">
        <?php if ($item['type'] == 0) : ?>
            <div class="card card-primary "
                 data-url="<?php echo $item['link']?>"
                <?php if ($item['external']) : ?> data-target="_blank" <?php endif; ?>
            >
                <div class="card-header">
                    <?php if ($item['state'] == 'running') : ?>
                        <span class="label label-success">
                                    <?php echo gT("Active"); ?>
                                </span>
                    <?php elseif ($item['state'] == 'inactive') : ?>
                        <span class="label label-default">
                                    <?php echo gT("Inactive"); ?>
                                </span>
                    <?php elseif ($item['state'] == 'expired') : ?>
                        <span class="label label-danger">
                                    <?php echo gT("Expired"); ?>
                                </span>
                    <?php endif; ?>
                    <div class="card-title">
                        <?php echo viewHelper::filterScript(gT($item['survey']->defaultlanguage->surveyls_title)); ?>
                    </div>
                    <span class="card-detail"><?php echo $item['survey']->creationdate?></span>
                </div>
                <div class="card-footer">
                    <div class="content">
                        <div>
                            <?php echo $item['survey']->countFullAnswers == 0 ? 'No' : $item['survey']->countFullAnswers?> responses
                        </div>
                        <div class="icons">

                                <?php if ($item['survey']->active === "N" && $item['survey']->groupsCount > 0 && $item['survey']->getQuestionsCount() > 0) : ?>
                                    <a href="<?= App()->createUrl("/surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/" . $item['survey']->sid) ?? '#'?>"
                                       class="active"
                                       data-bs-toggle="tooltip"
                                       data-bs-original-title="<?=gT('Activate') ?>"
                                    >
                                        <i class="ri-check-line"></i>
                                    </a>
                                <?php elseif ($item['survey']->active !== "Y") : ?>
                                    <a href="<?= App()->createUrl("/surveyAdministration/view?iSurveyID=" . $item['survey']->sid) ?? '#'?>"
                                       class="active"
                                       data-bs-toggle="tooltip"
                                       data-bs-original-title="<?=gT('Edit survey')?>"
                                    >
                                        <i class="ri-edit-line"></i>
                                    </a>
                                <?php elseif ($item['survey']->active === "Y") : ?>
                                    <a href="<?= App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/" . $item['survey']->sid) ?? '#'?>"
                                       class="active"
                                       data-bs-toggle="tooltip"
                                       data-bs-original-title="<?=gT('Statistics') ?>"
                                    >
                                        <i class="ri-line-chart-line"></i>
                                    </a>
                                <?php endif; ?>

                            <?php echo $item['survey']->getButtons(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($item['type'] == 2) : ?>
            <div class="card card-primary card-clickable card-link"
                 data-url="<?php echo $item['link']?>"
                <?php if ($item['color']) : ?> style="color:<?php echo $item['color']?>;border-color:<?php echo $item['color']?>" <?php endif; ?>
                <?php if ($item['external']) : ?> data-target="_blank" <?php endif; ?>
            >
                <div class="card-body">
                    <i class="<?php echo $item['icon']?>"></i>
                    <?php echo $item['text']?>
                </div>

            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
