<div id="notif-container col-12" style="position: relative; top: 70px;" >
    <?php foreach($aMessage as $message):?>
        <?php
        if (!isset($message['type']) || (isset($message['type']) && !in_array($message['type'],array('success','info','warning','danger','error'))))
        {
            $message['type']='success';
        }
        if ($message['type']=='error')
        {
            $message['type']='danger';
        }
        ?>

        <div class="alert alert-<?php echo $message['type'];?> alert-dismissible col-12" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             <?php echo $message['message'];?>
        </div>
    <?php endforeach;?>
</div>
