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
            <label for='copymail'>
                <?php eT("Send me a copy:"); ?>
                <input id='copymail' name='copymail' type='checkbox' class='checkboxbtn' value='1'/>
            </label>
        </div>
        <div class="mb-3">
            <label for='subject'>
                <?php eT("Subject:"); ?>
            </label>
            <input type='text' id='subject' size='50' name='subject' value='' class="form-control"/>
        </div>
        <div class="mb-3">
            <label for='body'>
                <?php eT("Message:"); ?>
            </label>
            <textarea cols='50' rows='4' id='body' name='body' class="form-control"></textarea>
        </div>

        <input type='hidden' name='action' value='mailsendusergroup'/>
        <input type='hidden' name='ugid' value='<?php echo $ugid; ?>'/>
        <?php echo CHtml::endForm() ?>
    </div>
</div>
