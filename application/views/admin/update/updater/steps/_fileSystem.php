<?php
/**
 * This view display the changed files, and they're permission status
 * 
 * @var array $readonlyfiles an array containing the list of readonlyfiles
 * @var array $existingfiles an array containing the list of existingfiles
 * @var array $modifiedfiles an array containing the list of modifiedfiles
 * @var int $destinationBuild the destination build
 */
?>
<?php $urlNew = Yii::app()->createUrl("admin/globalsettings", array("update"=>'checkFiles', 'destinationBuild' => $destinationBuild, 'access_token' => $access_token)); ?>

<h2 class="maintitle"><?php eT('Checking existing LimeSurvey files...'); ?></h2>

<?php if($html_from_server!=""):?>
    <div>
        <?php echo $html_from_server;?>
    </div>
<?php endif;?>

<div class="updater-background">
    
    <?php $this->renderPartial("./update/updater/steps/textaeras/_readonlyfiles", array("readonlyfiles"=>$readonlyfiles) );?>
    <?php $this->renderPartial("./update/updater/steps/textaeras/_existingfiles", array("existingfiles"=>$existingfiles) );?>
    <?php $this->renderPartial("./update/updater/steps/textaeras/_modifiedfiles", array("modifiedfiles"=>$modifiedfiles) );?>       

    
    
    <?php if (count($readonlyfiles)>0):?>
            <br />
            <p>
                <!--
                <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
                    <span class="ui-button-text"><?php echo gT('Check again');?></span>
                </a>
                -->
                <?php
                    $url = Yii::app()->createUrl('/admin/globalsettings');
                    echo CHtml::beginForm($url, 'post');
                    echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
                    echo CHtml::hiddenField('access_token' , $access_token);
                    echo CHtml::hiddenField('update' , 'checkFiles');
                    //echo CHtml::hiddenField('datasupdateinfo' , $datasupdateinfo);
                    echo '<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="'.Yii::app()->createUrl("admin/globalsettings").'" role="button" aria-disabled="false">
                            <span class="ui-button-text">'.gT("Cancel").'</span>
                        </a>';
                    echo CHtml::submitButton(gT('Check again'), array("class"=>"ui-button ui-widget ui-state-default ui-corner-all")); 
                    echo CHtml::endForm();
                ?>              
            </p>
    <?php else: ?>
        <p>
            <?php 
                $url = Yii::app()->createUrl('/admin/update/sa/backup');
                echo CHtml::beginForm($url, 'post', array("id"=>"launchBackupForm"));
                echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
                echo CHtml::hiddenField('access_token' , $access_token);
                echo CHtml::hiddenField('datasupdateinfo' , $datasupdateinfo);
                echo '<a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="'.Yii::app()->createUrl("admin/globalsettings").'" role="button" aria-disabled="false">
                        <span class="ui-button-text">'.gT("Cancel").'</span>
                    </a>';
                echo CHtml::submitButton(sprintf(gT('Continue')), array("class"=>"ui-button ui-widget ui-state-default ui-corner-all")); 
                echo CHtml::endForm();
            ?>              
        </p>
    <?php endif;?>
</div>

<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchBackupForm').comfortUpdateNextStep({'step': 3});  
</script>