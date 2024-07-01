<?php
/**  @var  integer $ugid */
?>

<div class="col-12 list-surveys">

    <div class="row">
        <?php echo CHtml::form(
            ["userGroup/MailToAllUsersInGroup/ugid/{$ugid}"],
            'post',
            ['class' => 'col-lg-6 offset-lg-3', 'id' => 'mailusergroup', 'name' => 'mailusergroup']
        ); ?>
        <div class="mb-3">
            <label class="form-label" for='subject'><?php eT("Subject:"); ?></label>
            <input class="form-control" type='text' id='subject' size='50' name='subject' value='' />
        </div>
        <div class="mb-3">
            <label class="form-label" for='body'><?php eT("Message:"); ?></label>
            <textarea cols='50' rows='4' id='body' name='body' class="form-control"></textarea>
        </div>
        <div class="form-check">
            <input class="form-check-input" id='copymail' name='copymail' type='checkbox' value='1'/>
            <label class="form-check-label" for='copymail'><?php eT("Send me a copy"); ?></label>
        </div>

        <input type='hidden' name='action' value='mailsendusergroup'/>
        <input type='hidden' name='ugid' value='<?php echo $ugid; ?>'/>
        <?php echo CHtml::endForm() ?>
    </div>
</div>
