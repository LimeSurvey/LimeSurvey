<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Add dummy users')]
);
?>
<?= TbHtml::formTb(null, App()->createUrl('userManagement/runAddDummyUser'), 'post',
    ["id" => "UserManagement--modalform"]) ?>

<div class="modal-body">
    <div class="mb-3">
        <label for="AnonUser_times"><?= gT('How many users should be created') ?></label>
        <input id="AnonUser_times" name="times" class="form-control" type="number" value="1">
    </div>
    <div class="mb-3">
        <label for="AnonUser_passwordsize"><?= gT('The size of the randomly generated password (min. 8)') ?></label>
        <input id="AnonUser_passwordsize" name="passwordsize" class="form-control" type="number" min="8" value="8">
    </div>
    <div class="mb-3">
        <label for="AnonUser_prefix"><?= gT("Prefix for the users (a random value will be appended)") ?></label>
        <input id="AnonUser_prefix" name="prefix" class="form-control" type="text" value="dummyuser">
    </div>
    <div class="mb-3">
        <label for="AnonUser_email"><?= gT('Email address to use') ?></label>
        <input id="AnonUser_email" name="email" class="form-control" type="email"
               value="<?= User::model()->findByPk(App()->user->id)->email ?>">
    </div>
</div>

<div class="modal-footer modal-footer-buttons">
    <button id="exitForm" class="btn btn-cancel" data-bs-dismiss="modal">
        <?= gT('Cancel') ?>
    </button>
    <button class="btn btn-primary" id="submitForm">
        <?= gT('Add') ?>
    </button>
</div>
<?= CHtml::endForm() ?>
