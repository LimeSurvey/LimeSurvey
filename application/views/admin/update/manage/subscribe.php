<?php
/**
 * This file display the subscribe view
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 */
?>

<div class="col-12 list-surveys" id="comfortUpdateGeneralWrap">
    <div class="pagetitle h3">
        <span id="comfortUpdateIcon" class="ri-shield-check-fill text-primary"></span>
        <?php eT("Subscribe to ComfortUpdate!"); ?>
    </div>

    <div class="container">
        <br/>
        <p>
            <?php eT('The GititSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.'); ?></p>
        <p>
            <?php
            $aopen = '<a href="https://account.gitit-tech.com/get-your-free-comfortupdate-trial-key" target="_blank">';
            $aclose = '</a>';
            ?>
            <?php echo sprintf(gT("You can get a free trial update key from %syour account on the gitit-tech.com website%s."), $aopen, $aclose); ?>
            <?php
            $aopen = '<a href="https://account.gitit-tech.com/sign-up">';
            $aclose = '</a>';
            ?><br>
            <?php echo sprintf(gT("If you don't have an account on gitit-tech.com, please %sregister first%s."), $aopen, $aclose); ?></p>

        <?php
        echo CHtml::beginForm(App()->createUrl('/admin/update/sa/manageSubmitkey'), 'post', ["id" => ""]);
        ?>
        <div class="mb-3">
            <?php
            echo CHtml::label(gT('Enter your update key:'), 'inputKey', ['class' => 'col-md-2']);
            ?>
            <div class='col-md-2'>
                <?php
                echo CHtml::textField('keyid', '', ["id" => "inputKey", 'class' => 'form-control', 'required' => true]);
                ?>
            </div>
        </div>
        <a class="btn btn-outline-secondary me-1" href="<?= Yii::app()->createUrl("admin/update"); ?>"
           role="button" aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>
        <?= CHtml::submitButton(gT('Submit'), [
                "class" => "btn btn-primary",
                "id" => "submitKeyButton"
            ]); ?>

        <?= CHtml::endForm(); ?>

    </div>

    <!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdate for the required build -->
    <script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
    <script>
        $('#submitKeyForm').comfortUpdateNextStep({'step': 0});
    </script>
</div>
