<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-6">
        <h2><?php echo $title; ?></h2>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $descp,
            'type' => 'success',
        ]);
        ?>
        <legend> <?php eT("Administrator credentials"); ?></legend>
        <?php eT("Username:"); ?> <?php echo $user; ?> <br />
        <?php eT("Password:"); ?> <?php echo $pwd; ?>

        <div style="text-align: center">
            <a id="ls-administration" href="<?php echo $this->createUrl("/admin"); ?>" class="btn btn-outline-secondary btn btn-outline-secondary-default btn btn-outline-secondary-lg"><?php eT("Administration")?></a>
        </div>
    </div>

    <div class="col-md-3">

        <div class="thumbnail" style="padding: 1em;">
            <img class="rounded mx-auto d-block m-3" style="width: 50%;" src="<?php echo Yii::app()->baseUrl; ?>/installer/images/comfortupdate-logo.png" alt="ComfortUpdate Logo">
            <div class="caption">
                <h3><?= gT("ComfortUpdate"); ?></h3>
                <p>
                    <?= gT("Subscribe to ComfortUpdate to get access to:"); ?>
                    <ul>
                        <li><?= gT("Easy updates"); ?></li>
                        <li><?= gT("Technical support"); ?>*</li>
                        <li><?= gT("Legacy and LTS versions"); ?></li>
                    </ul>
                </p>
                <p class="text-center d-grid gap-2">
                    <a href="https://community.gitit-tech.com/comfort-update-extension/" class="btn btn-primary btn-block" role="button" target="_blank">
                        <?= gT("Free trial"); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>

</div>
