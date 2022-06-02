<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h3><span class="fa fa-language text-success"></span>&nbsp;&nbsp;<?php eT("Translate survey"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php
            echo CHtml::form(["admin/translate/sa/index", 'surveyid' => $surveyid], 'get', ['id' => 'translatemenu']);
            ?>
            <?php echo $adminmenu; ?>
            </form>
        </div>
    </div>

    <div class="row">
