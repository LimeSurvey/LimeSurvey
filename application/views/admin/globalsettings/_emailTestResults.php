<div class="pagetitle h3"><?php eT("Test email settings");?></div>

<div class="row" style="margin-bottom: 100px">
    <div class="col-12">
        <div class="jumbotron message-box <?= !$success ? 'message-box-error' : ''?>">
            <h2><?php eT("Email test result"); ?></h2>
            <p class="h4 <?= !$success ? 'text-danger' : 'text-success'?>"><?php echo $message;?></p>
            <?php if(!$success) { ?>
                <div> <?php echo $maildebug ?></div>
            <?php } ?>       
            <p>
                <a class="btn btn-lg btn-primary" href='<?php echo $this->createUrl('admin/globalsettings');?>'><?php eT("Back to settings"); ?></a>
            </p>
        </div>
    </div>
</div>
