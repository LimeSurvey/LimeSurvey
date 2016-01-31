<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-9">
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <b> <?php eT("Administrator credentials"); ?>:</b><br /><br />
        <?php eT("Username"); ?>: <?php echo $user; ?> <br />
        <?php eT("Password"); ?>: <?php echo $pwd; ?>
        <div style="text-align: center">
            <a href="<?php echo $this->createUrl("/admin"); ?>" class="btn btn-default btn btn-default-default btn btn-default-lg"><?php eT("Administration")?></a>
        </div>
    </div>
</div>
