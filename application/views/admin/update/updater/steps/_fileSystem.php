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
<?php $urlNew = Yii::app()->createUrl("admin/update", array("update" => 'checkFiles', 'destinationBuild' => $destinationBuild, 'access_token' => $access_token)); ?>

<h3 class="maintitle"><?php eT('Checking existing GititSurvey files...'); ?></h3>

<?php if ($html_from_server != "") :?>
    <div>
        <?php echo $html_from_server;?>
    </div>
<?php endif;?>

<div class="updater-background">

    <?php $this->renderPartial("./update/updater/steps/textaeras/_readonlyfiles", array("readonlyfiles" => $readonlyfiles));?>
    <?php $this->renderPartial("./update/updater/steps/textaeras/_existingfiles", array("existingfiles" => $existingfiles));?>
    <?php $this->renderPartial("./update/updater/steps/textaeras/_modifiedfiles", array("modifiedfiles" => $modifiedfiles));?>



    <?php if (count($readonlyfiles) > 0) :?>
            <br />
        <div class="col-12 mt-2">
            <?php
            $url = Yii::app()->createUrl('/admin/update');
            echo CHtml::beginForm($url, 'post');
            echo CHtml::hiddenField('destinationBuild', $destinationBuild);
            echo CHtml::hiddenField('access_token', $access_token);
            echo CHtml::hiddenField('update', 'checkFiles');
            echo '<a class="btn btn-cancel me-1" href="' . Yii::app()->createUrl("admin/update") .
                '" role="button" aria-disabled="false">' .
                gT("Cancel") .
                '</a>&nbsp;';
            echo CHtml::submitButton(gT('Check again', 'unescaped'), array("class" => "btn btn-outline-secondary"));
            echo CHtml::endForm();
            ?>
        </div>>
    <?php else : ?>
        <div class="col-12 mt-2">
            <?php
                $url = Yii::app()->createUrl('/admin/update/sa/backup');
                echo CHtml::beginForm($url, 'post', array("id"=>"launchBackupForm"));
                echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
                echo CHtml::hiddenField('access_token' , $access_token);
                echo '<a class="btn btn-cancel me-1" href="'.Yii::app()->createUrl("admin/update").'" role="button" aria-disabled="false">
                        '.gT("Cancel").'
                    </a>';
                echo CHtml::submitButton(sprintf(gT('Continue')), array("class" => "btn btn-primary"));
                echo CHtml::endForm();
            ?>
        </div>
    <?php endif;?>
</div>

<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('#launchBackupForm').comfortUpdateNextStep({'step': 3});
</script>
