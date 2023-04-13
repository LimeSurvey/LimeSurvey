<?php
/** @var $useradddialog */
/** @var $addableUsers */
/** @var $ugid */
?>

<?php
if (!empty($useradddialog)) {
    ?>
    <?php echo CHtml::form(["userGroup/AddUserToGroup"], 'post'); ?>
    <table class='users'>
        <tbody>
        <tr>
            <td>
                <div class="row">
                    <div class="col-xl-8">
                        <?php echo CHtml::dropDownList('uid', '-1', $addableUsers, ['class' => "form-select col-xl-4"]); ?>
                        <input name='ugid' type='hidden' value='<?php echo $ugid; ?>'/>
                    </div>
                    <div class="col-xl-4">
                        <input type='submit' value='<?php eT("Add user"); ?>' class="btn btn-outline-secondary"/>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <?php echo CHtml::endForm() ?>
    <?php
}
?>
