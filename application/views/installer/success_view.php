<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-6">
        <h2><?php echo $title; ?></h2>
        <div class='alert alert-success'><?php echo $descp; ?></div>
        <legend> <?php eT("Administrator credentials"); ?></legend>
        <?php eT("Username:"); ?> <?php echo $user; ?> <br />
        <?php eT("Password:"); ?> <?php echo $pwd; ?>

        <div style="text-align: center">
            <a id="ls-administration" href="<?php echo $this->createUrl("/admin"); ?>" class="btn btn-default btn btn-default-default btn btn-default-lg"><?php eT("Administration")?></a>
        </div>
    </div>

    <div class="col-md-3">

        <?php /* Bootstrap 5 version
                <div class="card">
                    <img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/comfortupdate-logo.png" class="card-img-top" alt="ComfortUpdate Logo">
                    <div class="card-body">
                        <h5 class="card-title">ComfortUpdate</h6>
                        <p class="card-text">
                            Subscribe to ComfortUpdate to get access to:
                            <li>
                                <ul>Easy updates</ul>
                                <ul>Technical support*</ul>
                                <ul>More?</ul>
                            </li>
                        </p>
                        <a href="https://community.limesurvey.org/comfort-update-extension/" class="card-link">Free trial</a>
                    </div>
                </div>
         */ ?>

        <div class="thumbnail" style="padding: 1em;">
            <img style="width: 50%;" src="<?php echo Yii::app()->baseUrl; ?>/installer/images/comfortupdate-logo.png" alt="ComfortUpdate Logo">
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
                <p class="text-center">
                    <a href="https://community.limesurvey.org/comfort-update-extension/" class="btn btn-primary btn-block" role="button" target="_blank">
                        <?= gT("Free trial"); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>

</div>
