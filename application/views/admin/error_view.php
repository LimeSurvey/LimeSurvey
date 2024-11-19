<div class="jumbotron message-box message-box-error">
        <h2><?php echo $sHeading; ?></h2>
        <p class="lead"><?php eT('Error'); ?></p>
        <p><?php echo eT($sMessage); ?></p>
        <p><a class="btn btn-lg btn-primary" href="<?php echo $this->createUrl("admin/"); ?>" role="button"><?php eT("Ok"); ?></a></p>
</div>
