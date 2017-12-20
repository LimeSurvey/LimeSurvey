<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3><?php echo eT('Time statistics'); ?></h3>
    <script type='text/javascript'>
        var strdeleteconfirm='<?php echo eT('Do you really want to delete this response?', 'js'); ?>';
        var strDeleteAllConfirm='<?php echo eT('Do you really want to delete all marked responses?', 'js'); ?>';
    </script>
    <?php /*
    <div class='menubar'>
        <div class='menubar-title ui-widget-header'>
            <strong><?php eT("Data view control"); ?></strong>
        </div>
        <div class='menubar-main'>
            <?php if (!Yii::app()->request->getPost('sql'))
            { ?>
                <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/0/limit/$limit"); ?>' title='<?php eT("Show start..."); ?>' >
                    <span title='<?php eT("Show start..."); ?>'  name='DataBegin'  class="icon-databegin text-success"></span>
                </a>
                <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/$last/limit/$limit"); ?>' title='<?php eT("Show previous.."); ?>'>
                    <span title='<?php eT("Show previous..."); ?>'  name='DataBack'  class="icon-databack text-success"></span>
                </a>

                <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/$next/limit/$limit"); ?>' title='<?php eT("Show next..."); ?>'>
                    <span title='<?php eT("Show next..."); ?>'  name='DataForward'  class="icon-dataforward text-success"></span>
                </a>
                <a href='<?php echo $this->createUrl("/admin/responses/sa/time/surveyid/$iSurveyId/start/$end/imit/$limit"); ?>' title='<?php eT("Show last..."); ?>'>
                    <span title='<?php eT("Show last..."); ?>'  name='DataEnd'  class="icon-dataend text-success"></span>
                </a>

            <?php } ?>
            <?php echo CHtml::form(array("admin/responses/sa/time/surveyid/{$surveyid}/"), 'post', array('id'=>'browseresults')); ?>
                <font size='1' face='verdana'>

                <?php eT("Records displayed:"); ?> <input type='text' size='4' value='<?php echo $limit ?>' name='limit' id='limit' />
                <?php eT("Starting from:"); ?> <input type='text' size='4' value='<?php echo $start; ?>' name='start' id='start' />
                <input type='submit' value='<?php eT("Show"); ?>' />
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
            <th><?php eT('Actions'); ?></th>
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
                    <span id='imgDeleteMarkedResponses' title='<?php eT('Delete marked responses'); ?>' class="fa fa-trash"/>
                <?php } ?>
            </td>
        </tr>
    </tfoot>
    */ ?>
