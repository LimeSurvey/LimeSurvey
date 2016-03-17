<?php
/**
 * This view displays the Step 1 : pre-installation checks.
 * The javascript inject it inside the div#updaterContainer, in the _updater view. (like any steps)
 *
 * @var object $localChecks an object containing all the checks results
 * @var int $destinationBuild the destination build
 */
?>

<?php
        $urlNew = Yii::app()->createUrl("admin/update", array("update"=>'checkLocalErrors', 'destinationBuild' => $destinationBuild, 'access_token' => $access_token));
        $errors = FALSE;
?>

<h3 class="maintitle"><?php eT('Checking basic requirements...'); ?></h3>

<?php
    if( isset($localChecks->html) )
        echo $localChecks->html;
?>

<table class="table">
    <thead>
        <tr>
            <th class="span8"><?php eT('Available space in directory:');?></th>
            <th class="span2"  style="text-align: right"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($localChecks->files as $file):?>
            <?php if($file->freespace !== 'pass'): ?>
                <tr>
                    <td><?php echo $file->name;?></td>
                    <td class="<?php if($file->freespace){echo "success" ;}else{echo "error";}?>" style="text-align: right">
                        <?php if($file->freespace): ?>
                            <?php eT('OK');?>
                        <?php else: ?>
                            <?php eT('Not enough space'); ?>
                            <?php $errors = true; $ignore = true; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if($errors): ?>
    <div>
    <em>
        <?php eT("Note : In some hosting, like shared hosting, it can happen that the available free space is not correctly evaluated. If you checked manually that you have enough free space to update, please, just ignore this error."); ?>
    </em>
    <br/><br/>
    </div>
<?php endif;?>

<table class="table">
    <thead>
        <tr>
            <th class="col-sm-10"><?php eT('PHP version required');?></th>
            <th class="col-sm-2"  style="text-align: right"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="col-sm-10"><?php echo $localChecks->php->php_ver;?></td>
            <td class="col-sm-2 <?php if($localChecks->php->result){echo "success" ;}else{echo "error";}?>" style="text-align: right">
                <?php if($localChecks->php->result): ?>
                    <?php eT('OK');?>
                <?php else: ?>
                    <?php eT('Not enough space'); ?>
                    <?php printf(gT('PHP version is only %s'),$localChecks->php->local_php_ver);?>
                    <?php $errors = TRUE; $cant_ignore = true;?>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>

<table class="table">
    <thead>
        <tr>
            <th class="col-sm-10"><?php eT('Required PHP modules:');?></th>
            <th class="col-sm-12"  style="text-align: right"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($localChecks->php_modules as $name => $module):?>
        <tr>
            <td class="col-sm-10"><?php echo $name;?></td>
            <td class="col-sm-2 <?php if($module->installed){echo "success" ;}else{ if(isset($module->required)){echo "error";}else{echo "warning";}}?>" style="text-align: right">
                <?php if($module->installed): ?>
                    <?php eT('OK');?>
                <?php else: ?>
                    <?php if(isset($module->required)): ?>
                        <?php eT('No'); ?> !
                        <?php $errors = TRUE; $cant_ignore = true; ?>
                    <?php elseif(isset($module->optional)): ?>
                        <?php eT('No (but optional)'); ?>
                    <?php endif;?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if($errors): ?>
    <?php
    if($ignore && ! $cant_ignore )
    {
            $formUrl = Yii::app()->getController()->createUrl("admin/update/sa/changeLog/");
            echo CHtml::beginForm($formUrl, 'post', array("id"=>"launchChangeLogForm"));
            echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
            echo CHtml::hiddenField('access_token' , $access_token);
    }
    ?>

<p>
    <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
        <?php eT("Cancel"); ?>
    </a>
    <a class="btn btn-default" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
        <?php eT('Check again');?>
    </a>

    <?php if($ignore  && ! $cant_ignore): ?>

        <?php
            echo CHtml::submitButton(gT('Ignore','unescaped'), array('id'=>'Ignorestep1launch', "class"=>"btn btn-default"));
        ?>
    <?php endif;?>
</p>
<?php if($ignore  && ! $cant_ignore)
            echo CHtml::endForm();
?>

<?php else:?>
<p>
    <?php echo gT('Everything looks alright. Please proceed to the next step.');?>

    <?php
        $formUrl = Yii::app()->getController()->createUrl("admin/update/sa/changeLog/");
        echo CHtml::beginForm($formUrl, 'post', array("id"=>"launchChangeLogForm"));
        echo CHtml::hiddenField('destinationBuild' , $destinationBuild);
        echo CHtml::hiddenField('access_token' , $access_token);
    ?>
        <a class="btn btn-default" href="<?php echo Yii::app()->createUrl("admin/update"); ?>" role="button" aria-disabled="false">
            <?php eT("Cancel"); ?>
        </a>

    <?php
        echo CHtml::submitButton(gT('Continue','unescaped'), array('id'=>'step1launch', "class"=>"btn btn-default"));
        echo CHtml::endForm();
    ?>
</p>

<?php endif;?>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the comfortupdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
$('#launchChangeLogForm').comfortUpdateNextStep({'step': 1});
</script>
