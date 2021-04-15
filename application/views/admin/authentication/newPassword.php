<?php

?>

<div class="container-center">
    <?php $form = $this->beginWidget('TbActiveForm', array(
        'id' => 'newpassword-resettpassword',
        'action' => App()->createUrl('authentication/sa/newPassword'),
    )); ?>

    <div class="row ls-space margin top-5 hidden" id="utility_change_password_container">
        <div class="row ls-space margin top-5">
            <label for="password" class="required" required><?= gT("Password safety") ?> <span
                        class="required">*</span></label>
            <input name="password"
                   placeholder='********'  id="password"
            class="form-control" type="password">
        </div>
        <div class="row ls-space margin top-5">
            <label for="passwordRepeat" class="required" required><?= gT("Password safety") ?> <span
                        class="required">*</span></label>
            <input name="passwordRepeat"
                   placeholder='********'  id="password_repeat"
            class="form-control" type="password">
        </div>
        <div class="row ls-space margin top-5">
            <label class="control-label">
                <?= gT('Random password (suggestion):') ?>
            </label>
            <input type="text" class="form-control" readonly name="random_example_password"
                   value="<?= htmlspecialchars($randomPassword) ?>"/>
        </div>
    </div>

    <?php
        $this->endWidget();
    ?>


</div>