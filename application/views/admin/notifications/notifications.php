<div id="notif-container" class="col-lg-12 content-right" style="z-index: 10100">
    <?php foreach($aMessage as $message):?>
        <?php if(!isset($message['type'])): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                     <?php echo $message['message'];?>
            </div>
        <?php else: ?>

            <?php if($message['type']=="success"): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>
            <?php elseif($message['type']=="error"):?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>
            <?php elseif($message['type']=="warning"):?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>
            <?php else:?>

                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>

            <?php endif;?>


        <?php endif;?>

    <?php endforeach;?>
</div>
