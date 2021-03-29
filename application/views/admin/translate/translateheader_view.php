<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <h3><span class="fa fa-language text-success" ></span>&nbsp;&nbsp;<?php eT("Translate survey"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
                echo CHtml::form(array("admin/translate/sa/index",'surveyid'=>$surveyid),'get',array('id'=>'translatemenu','class'=>'form-inline'));
            ?>
            <?php echo $adminmenu; ?>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 content-right">
            <h4>
                <?php eT("Translate survey");?>
            </h4>
    </div>
