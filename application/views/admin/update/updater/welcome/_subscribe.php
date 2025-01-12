<?php
/**
* This file display the subscribe view
* The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
*/
?>
<h3 class="maintitle"><?php eT("Subscribe to ComfortUpdate!");?></h3>

<?php
if( isset($serverAnswer->html) )
    echo $serverAnswer->html;
?>

<div class="updater-background">
    <br/>
    <p>
    <?php eT('The GititSurvey ComfortUpdate is a great feature to easily update to the latest version of LimeSurvey. To use it you will need an update key.');?></p><p>
        <?php
        $aopen  = '<a href="https://account.gitit-tech.com/get-your-free-comfortupdate-trial-key" target="_blank">';
        $aclose = '</a>';
        ?>
        <?php echo sprintf(gT("You can get a free trial update key from %syour account on the gitit-tech.com website%s."),$aopen, $aclose); ?>
        <?php
        $aopen  = '<a href="https://account.gitit-tech.com/sign-up">';
        $aclose = '</a>';
        ?><br>
    <?php echo sprintf(gT("If you don't have an account on gitit-tech.com, please %sregister first%s."),$aopen, $aclose);?></p>

    <?php
    $url = Yii::app()->createUrl('/admin/update/sa/submitkey');
    echo CHtml::beginForm($url, 'post', array("id"=>"submitKeyForm"));
    echo CHtml::hiddenField('destinationBuild', Yii::app()->request->getParam('destinationBuild',''));?>
    <div class="mb-3">
        <?php
        echo CHtml::label(gT('Enter your update key:'),'inputKey', array('class'=>'col-md-2'));
        ?>
        <div class='col-md-2'>
            <?php
            echo CHtml::textField('keyid', '', array("id"=>"inputKey",'class'=>'form-control','required' => true));
            ?>
        </div>
    </div>
    <?php
    echo CHtml::submitButton(gT('Submit'), array("class"=>"btn btn-outline-secondary", "id"=>"submitKeyButton"));
    ?>
    <a class="btn btn-cancel" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
        <?php eT("Cancel"); ?>
    </a>
    <?php echo CHtml::endForm();?>

</div>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#submitKeyForm').comfortUpdateNextStep({'step': 0});
</script>
