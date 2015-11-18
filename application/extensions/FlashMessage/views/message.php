<div id="notif-container col-lg-12" style="position: relative; top: 70px;" >
    <?php foreach($aMessage as $message):?>

        <?php if(!isset($message['type'])): ?>
            <div class="alert alert-success alert-dismissible col-lg-12" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>        
                     <?php echo $message['message'];?>
            </div>
        <?php else: ?>

            <?php if($message['type']=="success"): ?>
                <div class="alert alert-success alert-dismissible col-lg-12" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>
            <?php elseif($message['type']=="error"):?>
                <div class="alert alert-danger alert-dismissible col-lg-12" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>
            <?php else:?>

                <div class="alert alert-success alert-dismissible col-lg-12" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                         <?php echo $message['message'];?>
                </div>

            <?php endif;?>


        <?php endif;?>

    <?php endforeach;?>
</div>
