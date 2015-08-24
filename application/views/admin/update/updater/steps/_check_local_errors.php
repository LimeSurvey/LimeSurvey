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
        $urlNew = Yii::app()->createUrl("admin/globalsettings", array("update"=>'checkLocalErrors', 'destinationBuild' => $destinationBuild, 'access_token' => $access_token));
        $errors = FALSE; 
?>

<h2 class="maintitle"><?php eT('Checking basic requirements...'); ?></h2>

<?php 
    if( isset($localChecks->html) )
        echo $localChecks->html;
?>


<ul>
<?php // foreach($localChecks as $check):?>
    
<?php foreach($localChecks->files as $file):?>
    <li>
        <strong><?php echo $file->name;?> :</strong>        
    </li>
    <li>
        <ul>
        <?php if($file->writable !== 'pass'): ?>
            <li>
                <span class="checkLine"><?php eT('Writable'); ?> :</span>  
                <?php if($file->writable): ?>
                        <span class="success resultLine"><?php eT('OK');?></span>
                <?php else: ?>
                        <span class="errortitle resultLine"><?php eT('Not writable'); ?> !</span>
                        <?php $errors = true; $cant_ignore = true; ?>
                <?php endif;?>
            </li>
        <?php endif;?>
        <?php if($file->freespace !== 'pass'): ?>
            <li>
                <span class="checkLine"><?php eT('Available space');?> :</span>
                <?php if($file->freespace): ?>
                    <span class="success resultLine"><?php eT('OK');?></span>
                <?php else: ?>
                    <span class="errortitle resultLine"> <?php eT('Not enough space'); ?> !</span>
                    <?php $errors = true; $ignore = true; ?>
                <?php endif;?>
            </li>           
        <?php endif;?>
        </ul>
    </li>
<?php endforeach; ?>
    
    <li>
        <span class="checkLine"><?php printf(gT('PHP version %s required'),$localChecks->php->php_ver);?> :</span>
        <?php if($localChecks->php->result):?>
            <span class="success resultLine" ><?php eT('OK');?></span>
        <?php else:?>
            <span class="errortitle resultLine"  ><?php printf(gT('PHP version is only %s'),$localChecks->php->local_php_ver);?></span>
            <?php $errors = TRUE; $cant_ignore = true;?>
        <?php endif;?>
    </li>

<?php foreach($localChecks->php_modules as $name => $module):?>
    <li>
        <strong><?php echo $name;?> :</strong>      
    </li>
    <li>
        <ul>
            <li>
                <span class="checkLine"><?php eT('Installed'); ?> :</span>
                <?php if($module->installed): ?>
                        <span class="success resultLine" ><?php eT('OK');?></span>
                <?php else: ?>
                    <?php if(isset($module->required)): ?>
                        <span class="errortitle resultLine"  ><?php eT('No'); ?> !</span>
                        <?php $errors = TRUE; $cant_ignore = true; ?>
                    <?php elseif(isset($module->optional)): ?>
                        <span class="errortitle resultLine"  ><?php eT('No (but optional)'); ?></span>
                    <?php endif;?>                      
                <?php endif;?>
            </li>
        </ul>
    </li>
<?php endforeach; ?>

    
</ul>




<?php if($errors): ?>
<p>
    <strong><?php eT('When checking your installation we found one or more problems. Please check for any error messages above and fix these before you can proceed.'); ?></strong>
    <?php // TODO : a new step request by url... ?>
</p>

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
    <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
        <span class="ui-button-text"><?php eT("Cancel"); ?></span>
    </a>    
    <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo $urlNew;?>" role="button" aria-disabled="false">
        <span class="ui-button-text"><?php eT('Check again');?></span>
    </a>

    <?php if($ignore  && ! $cant_ignore): ?>

        <?php 
            echo CHtml::submitButton(gT('Ignore'), array('id'=>'Ignorestep1launch', "class"=>"ui-button ui-widget ui-state-default ui-corner-all")); 
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
        <a class="button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only limebutton" href="<?php echo Yii::app()->createUrl("admin/globalsettings"); ?>" role="button" aria-disabled="false">
            <span class="ui-button-text"><?php eT("Cancel"); ?></span>
        </a>         
    
    <?php 
        echo CHtml::submitButton(gT('Continue'), array('id'=>'step1launch', "class"=>"ui-button ui-widget ui-state-default ui-corner-all")); 
        echo CHtml::endForm(); 
    ?>      
</p>

<?php endif;?>

<!-- this javascript code manage the step changing. It will catch the form submission, then load the ComfortUpdate for the required build -->
<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/scripts/admin/comfortupdate/comfortUpdateNextStep.js"></script>
<script>
$('#launchChangeLogForm').comfortUpdateNextStep({'step': 1});   
</script>