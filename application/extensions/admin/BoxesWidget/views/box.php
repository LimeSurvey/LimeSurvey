<?php foreach ($items as $item) : ?>
    <div class="col">
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
                        <?php echo $item['survey']->getButtons(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
