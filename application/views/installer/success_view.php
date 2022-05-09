<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-lg-9">
        <h2><?php echo $title; ?></h2>
        <div class='alert alert-success'><?php echo $descp; ?></div>
        <legend> <?php eT("Administrator credentials"); ?></legend>
        <?php eT("Username:"); ?> <?php echo $user; ?> <br />
        <?php eT("Password:"); ?> <?php echo $pwd; ?>
        <div style="text-align: center">
            <a id="ls-administration" href="<?php echo $this->createUrl("/admin"); ?>" class="btn btn-outline-secondary btn btn-outline-secondary-default btn btn-outline-secondary-lg"><?php eT("Administration")?></a>
        </div>
    </div>
</div>
