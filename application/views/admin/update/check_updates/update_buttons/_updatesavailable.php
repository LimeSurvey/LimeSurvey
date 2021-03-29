<?php
/**
 * This view display the buttons "use ComfortUpdate".
 * It is injected by the javascript inside the li#udapteButtonsContainer, in the _checkButton view.
 * @var obj updateInfos the update informations provided by the update server
 * @var obj $clang : the translate object, now moved to global function TODO : remove it
 */
?>
<?php
    // First we check if the server provided a specific HTML message
    if(isset($updateInfos->html))
    {
        if($updateInfos->html != "")
            echo $updateInfos->html;
        // And we unset this html message for the loop on update versions don't crush on it
        unset($updateInfos->html);
    }
?>

<?php if(isset($updateInfos->alert)): // First we check if the server provided a specific alert message ?>
    <?php if($updateInfos->alert != ""):?>
        <!-- Alert from server -->
        <div class="alert alert-warning" role="alert">
            <?php echo $updateInfos->alert; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>


<div>
    <strong><?php echo gT('The following LimeSurvey updates are available:');?></strong>
</div>
<br/>
<br/>


<table class="items table">
    <!-- header -->
    <thead>
        <tr>
            <th>
                <?php eT('LimeSurvey version'); ?>
            </th>
            <th>
                <?php eT('Branch'); ?>
            </th>
            <th>
                <?php eT('Update type'); ?>
            </th>
            <th>

            </th>
        </tr>
    </thead>

    <!-- rows for each version -->
    <?php foreach ($updateInfos as $aUpdateVersion):?>
        <?php $aUpdateVersion = (array) $aUpdateVersion;?>
        <tr>

            <!-- update version -->
            <td>
                 <?php
                     // display infos about the update. e.g : "2.05+ (150508) (stable)"
                     echo $aUpdateVersion['versionnumber'];?> (<?php echo $aUpdateVersion['build'];?>)

                <?php if(isset($aUpdateVersion['html'])):?>
                    <?php if($aUpdateVersion['html']!=''):?>
                        <?php echo $aUpdateVersion['html'];?>
                    <?php endif;?>
                <?php endif;?>
            </td>

            <!-- stable / unstable -->
            <?php if ($aUpdateVersion['branch']!='master'):?>
                <td class="text-warning">
                    <?php  eT('unstable'); ?>
                </td>
            <?php else: ?>
                <td>
                    <?php eT('stable');?>
                </td>
            <?php endif;?>

            <!-- security / regular -->
            <?php if($aUpdateVersion['security_update']):?>
            <td class="text-warning">
                    <?php eT("Security update");?>
            </td>
            <?php else: ?>
            <td>
                <?php eT("Regular update");?>
            </td>
            <?php endif; ?>

            <!-- button -->
            <td class="text-right">
                <!-- The form launching an update process. First step is the welcome message. The form is not submitted, but catch by the javascript inserted in the end of this file -->
                <?php echo CHtml::beginForm(App()->createUrl('admin/update/sa/getwelcome'), 'post', array('class'=>'launchUpdateForm')); ?>
                    <?php echo CHtml::hiddenField('destinationBuild' , $aUpdateVersion['build']); ?>

                    <!-- the button launching the update -->
                    <button type="submit" class="btn btn-default ajax_button launch_update">
                        <span style="height : 1em; margin-right : 0.5em;" class="icon-shield text-success"></span>
                        <?php eT("Use ComfortUpdate");?>
                    </button>

                     <?php if ($aUpdateVersion['branch']!='master'): ?>
                         <input type='button' class="ajax_button btn btn-default" onclick="window.open('https://community.limesurvey.org/downloads/', '_blank')" value='<?php eT("Download"); ?>' />
                     <?php else: ?>
                         <input type='button' class="ajax_button btn btn-default" onclick="window.open('https://community.limesurvey.org/downloads/', '_blank')" value='<?php eT("Download"); ?>' />
                     <?php endif; ?>

                 <?php echo CHtml::endForm(); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/assets/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
    $('.launchUpdateForm').comfortUpdateNextStep({'step': 0});
</script>
