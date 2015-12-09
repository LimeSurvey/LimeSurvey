
<?php

function dirReport($dir, $write)
{
    $error = 0;

    if ($dir == "Found")
    {
       $a = gT("Found");
    } else
    {
       $error = 1;
       $a = gT("Not found");
    }

    if ($write == "Writable")
    {
       $b = gT("Writable");
    } else
    {
       $error = 1;
       $b = gT("Unwritable");
    }

    if ($error)
    {
       return '<font color="red">'.$a.' &amp; '.$b.'</font>';
    }
    else
    {
       return $a.' &amp; '.$b;
    }
}

?>
<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="span9">
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <fieldset>
        <legend><?php eT("Minimum requirements"); ?></legend>

        <table class='table-striped'>
        <thead>
        <tr>
               <th>&nbsp;</th>
               <th class='text-center'><?php eT("Required"); ?></th>
               <th class='text-center'><?php eT("Current"); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
               <td><?php eT("PHP version"); ?></td>
               <td>5.3.3+</td>
               <td><?php if (isset($verror) && $verror) { ?><span style='font-weight:bold; color: red'><?php eT("Outdated"); ?>: <?php echo $phpVersion; ?></span>
               <?php } else { ?><?php echo $phpVersion ; ?> <?php } ?></td>
        </tr>
        <tr>
               <td><?php eT("Minimum memory available"); ?></td>
               <td>64MB</td>
               <td><?php
               if (isset($bMemoryError) && $bMemoryError) { ?><span style='font-weight:bold; color: red'><?php eT("Too low"); ?>: <?php echo $convertPHPSizeToBytes(ini_get('memory_limit'))/1024/1024; ?>MB</span>
               <?php } elseif (ini_get('memory_limit')=='-1') eT("Unlimited"); else { echo convertPHPSizeToBytes(ini_get('memory_limit'))/1024/1024; echo ' MB';} ?></td>
        </tr>
        <tr>
               <td><?php eT("PHP PDO driver library"); ?></td>
               <td><?php eT("At least one installed"); ?></td>
               <td><?php if (count($dbtypes)==0) { ?><span style='font-weight:bold; color: red'><?php eT("None found"); ?></span>
               <?php } else { ?><?php echo implode(', ',$dbtypes); ?> <?php } ?></td>
        </tr>
        <tr>
               <td><?php eT("PHP mbstring library"); ?></td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Yes" /></td>
               <td><?php echo $mbstringPresent; ?></td>
        </tr>
        <tr>
               <td><?php eT("PHP/PECL JSON library"); ?></td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Yes" /></td>
               <td><?php echo $bJSONPresent; ?></td>
        </tr>
        <tr>
               <td>/application/config <?php eT("directory"); ?></td>
               <td><?php eT("Found & writable"); ?></td>
               <td><?php  echo dirReport($configPresent,$configWritable); ?></td>
        </tr>
        <tr>
               <td>/upload <?php eT("directory"); ?></td>
               <td><?php eT("Found & writable"); ?></td>
               <td><?php  echo dirReport($uploaddirPresent,$uploaddirWritable); ?></td>
        </tr>
        <tr>
               <td>/tmp <?php eT("directory"); ?></td>
               <td><?php eT("Found & writable"); ?></td>
               <td><?php  echo dirReport($tmpdirPresent,$tmpdirWritable); ?></td>
        </tr>
        <tr>
               <td><?php eT("Session writable"); ?></td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Check" /></td>
               <td><?php echo $sessionWritableImg; if (!$sessionWritable) echo '<br/>session.save_path: ' . session_save_path(); ?></td>
        </tr>
        </tbody>
        </table>
        </fieldset>
        <fieldset>
        <legend><?php eT('Optional modules'); ?></legend>
        <table class='table-striped'>
        <thead>
            <tr>
                   <th>&nbsp;</th>
                   <th><?php eT('Recommended'); ?></th>
                   <th><?php eT('Current'); ?></th>
            </tr>
        </thead>
        <tbody>
        <tr>
               <td>PHP GD library</td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Check" /></td>
               <td><?php echo $gdPresent ; ?></td>
        </tr>
        <tr>
               <td>PHP LDAP library</td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Check" /></td>
               <td><?php echo $ldapPresent ; ?></td>
        </tr>
        <tr>
               <td>PHP zip library</td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Check" /></td>
               <td><?php echo $zipPresent ; ?></td>
        </tr>
        <tr>
               <td>PHP zlib library</td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Check" /></td>
               <td><?php echo $zlibPresent ; ?></td>
        </tr>
        <tr>
               <td>PHP imap library</td>
               <td><img src="<?php echo Yii::app()->baseUrl; ?>/installer/images/tick-right.png" alt="Check" /></td>
               <td><?php echo $bIMAPPresent ; ?></td>
        </tr>
        </tbody>

        </table>
        </fieldset>
        <div class="row navigator">
            <div class="span3" >
                <input class="btn" type="button" value="<?php eT('Previous'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/license"); ?>', '_top')" />
            </div>
            <div class="span3">
                <input class="btn" type="button" value="<?php eT('Check again'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" />
            </div>
            <div class="span3">

                <?php if (isset($next) && $next== TRUE) { ?>
                <input class="btn" type="button" value="<?php eT('Next'); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/database"); ?>', '_top')" />
                <?php } ?>
            </div>
        </div>
    </div>
</div>