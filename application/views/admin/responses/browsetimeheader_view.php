<div class="header ui-widget-header"><?php echo $clang->eT('Time statistics'); ?></div>
<script type='text/javascript'>
    var strdeleteconfirm='<?php echo $clang->eT('Do you really want to delete this response?', 'js'); ?>';
    var strDeleteAllConfirm='<?php echo $clang->eT('Do you really want to delete all marked responses?', 'js'); ?>';
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Data view control"); ?></strong>
    </div>
    <div class='menubar-main'>
        <?php if (!Yii::app()->request->getPost('sql'))
        { ?>
            <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/0/limit/$limit"); ?>' title='<?php $clang->eT("Show start..."); ?>' >
                <img name='DataBegin' align='left' src='<?php echo $sImageURL; ?>databegin.png' alt='<?php $clang->eT("Show start..."); ?>' />
            </a>
            <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/$last/limit/$limit"); ?>' title='<?php $clang->eT("Show previous.."); ?>'>
                <img name='DataBack' align='left'  src='<?php echo $sImageURL; ?>databack.png' alt='<?php $clang->eT("Show previous.."); ?>' />
            </a>
            <img src='<?php echo $sImageURL; ?>/blank.gif' width='13' height='20' alt='' />
            <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/$next/limit/$limit"); ?>' title='<?php $clang->eT("Show next..."); ?>'>
                <img name='DataForward' align='left' src='<?php echo $sImageURL; ?>dataforward.png' alt='<?php $clang->eT("Show next.."); ?>' />
            </a>
            <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/$end/imit/$limit"); ?>' title='<?php $clang->eT("Show last..."); ?>'>
                <img name='DataEnd' align='left' src='<?php echo $sImageURL; ?>dataend.png' alt='<?php $clang->eT("Show last.."); ?>' />
            </a>
            <img src='<?php echo $sImageURL; ?>separator.gif' alt='' />
        <?php } ?>
        <?php echo CHtml::form(array("admin/responses/sa/time/surveyid/{$surveyid}/"), 'post', array('id'=>'browseresults')); ?>
            <font size='1' face='verdana'>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='31' height='20' alt='' />
            <?php $clang->eT("Records displayed:"); ?> <input type='text' size='4' value='<?php echo $limit ?>' name='limit' id='limit' />
            <?php $clang->eT("Starting from:"); ?> <input type='text' size='4' value='<?php echo $start; ?>' name='start' id='start' />
            <input type='submit' value='<?php $clang->eT("Show"); ?>' />
            </font>
        </form>
    </div>
</div>

<?php echo CHtml::form(array("admin/responses/sa/time/surveyid/{$surveyid}/"), 'post', array('id'=>'resulttableform')); ?>

<!-- DATA TABLE -->
<?php if ($fncount < 10) { ?>
    <table class='browsetable' style='width:100%'>
    <?php } else { ?>
    <table class='browsetable'>
    <?php } ?>

<thead>
    <tr>
        <th><input type='checkbox' id='selectall'></th>
        <th><?php $clang->eT('Actions'); ?></th>
        <?php
            foreach ($fnames as $fn)
            {
                if (!isset($currentgroup))
                {
                    $currentgroup = $fn[1];
                    $gbc = "odd";
                }
                if ($currentgroup != $fn[1])
                {
                    $currentgroup = $fn[1];
                    if ($gbc == "odd")
                    {
                        $gbc = "even";
                    }
                    else
                    {
                        $gbc = "odd";
                    }
                }
            ?>
            <th class='<?php echo $gbc; ?>'>
                <strong><?php echo flattenText($fn[1], true); ?></strong>
            </th>
            <?php } ?>
    </tr>
</thead>
<tfoot>
    <tr>
        <td colspan=<?php echo $fncount + 2; ?>>
            <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'delete')) { ?>
                <img id='imgDeleteMarkedResponses' src='<?php echo $sImageURL; ?>token_delete.png' alt='<?php $clang->eT('Delete marked responses'); ?>' />
            <?php } ?>
        </td>
    </tr>
</tfoot>
