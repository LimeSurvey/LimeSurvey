<?php
/**
* This file display the subscribe view
* The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
*/
?>
<div class="col-sm-12 list-surveys" id="comfortUpdateGeneralWrap">
    <h3><span id="comfortUpdateIcon" class="icon-shield text-success"></span><?php eT("Subscribe to ComfortUpdate!");?></h3>

    <div class="" style="width: 75%; margin: auto;">
        <br/>
        <p>
        <?php eT('The LimeSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.');?></p><p>
            <?php
            $aopen  = '<a href="https://account.limesurvey.org/get-your-free-comfortupdate-trial-key" target="_blank">';
            $aclose = '</a>';
            ?>
            <?php echo sprintf(gT("You can get a free trial update key from %syour account on the limesurvey.org website%s."),$aopen, $aclose); ?>
            <?php
            $aopen  = '<a href="https://account.limesurvey.org/sign-up">';
            $aclose = '</a>';
            ?><br>
        <?php echo sprintf(gT("If you don't have an account on limesurvey.org, please %sregister first%s."),$aopen, $aclose);?></p>

        <?php
        echo CHtml::beginForm(App()->createUrl('/admin/update/sa/manage_submitkey'), 'post', array("id"=>""));
        ?>
        <div class="form-group">
            <?php
            echo CHtml::label(gT('Enter your update key:'),'inputKey', array('class'=>'col-sm-2'));
            ?>
            <div class='col-sm-2'>
                <?php
                echo CHtml::textField('keyid', '', array("id"=>"inputKey",'class'=>'form-control','required' => true));
                ?>
            </div>
        </div>
        <?php echo CHtml::submitButton(gT('Submit'), array("class"=>"btn btn-default", "id"=>"submitKeyButton")); ?>

        <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>
        <?php echo CHtml::endForm();?>

    </div>

    <!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdate for the required build -->
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
    <script>
        $('#submitKeyForm').comfortUpdateNextStep({'step': 0});
    </script>
</div>
