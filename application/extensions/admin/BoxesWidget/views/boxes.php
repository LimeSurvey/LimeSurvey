<div class="container-fluid box-widget">
    <div class="row row-cols-<?php echo $boxesbyrow ?>">
        <?php foreach ($items as $item) : ?>
            <div class="col">
                <?php if ($item['type'] == 0) : ?>
                    <div class="card card-primary "
                         data-url="<?php echo $item['link']?>"
                        <?php if ($item['external']) : ?> data-target="_blank" <?php endif; ?>
                    >
                        <div class="card-header">
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
                                    <?php echo $item['icon']?>
                                        <i class="" class="survey-state" data-bs-toggle="tooltip" title="<?php echo $item['state']?>"></i>

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
    </div>
    <div class="btn-container">
        <a href="#" id="load-more" data-page="1" data-limit="8">Load more</a>
    </div>
</div>
