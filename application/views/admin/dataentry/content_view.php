<?php
    if (!empty($explanation))
    { ?>
    <tr class ='data-entry-explanation'><td class='data-entry-small-text' colspan='3' align='left'><?php echo $explanation; ?></td></tr>
    <?php } ?>

<tr class='<?php echo $bgc; ?>'>
    <td class='data-entry-small-text' valign='top' width='1%'><?php echo $deqrow['title']; ?></td>
    <td valign='top' align='right' width='30%'>
        <?php if ($deqrow['mandatory']=="Y") //question is mandatory
            // TODO - should be mandatory AND relevant
            { ?>
            <font color='red'>*</font>
            <?php } ?>
        <strong><?php
                //                    echo flattenText($deqrow['question']);
                echo $deqrow['question'];   // don't flatten if want to use EM.  However, may not be worth it as want dynamic relevance and question changes
        ?></strong></td>
    <td valign='top'  align='left' style='padding-left: 20px'>

    <?php if ($deqrow['help'])
        { ?>
        <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/help.gif' alt='<?php echo $blang->gT("Help about this question"); ?>' align='right' onclick="javascript:alert('Question <?php echo $deqrow['title']; ?> Help: <?php echo $hh; ?>')" />
        <?php } echo $sQuestionElement; ?>
                   </td>
                   </tr>
                   <tr class='data-entry-separator'><td colspan='3'></td></tr>