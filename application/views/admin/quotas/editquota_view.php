<div class="header ui-widget-header"><?php $clang->eT("Edit quota");?></div>
    <?php echo CHtml::form(array("admin/quotas/sa/modifyquota/surveyid/{$iSurveyId}"), 'post', array('id'=>'editquota','class'=>'form30')); ?>
        <ul>
        <li><label for='quota_name'><?php $clang->eT("Quota name:");?></label> <input id="quota_name" name="quota_name" type="text" size="30" maxlength="255" value="<?php echo $quotainfo['name'];?>" /></li>
        <li><label for='quota_limit'><?php $clang->eT("Quota limit:");?></label><input id="quota_limit" name="quota_limit" type="text" size="12" maxlength="8" value="<?php echo $quotainfo['qlimit'];?>" /></li>
        <li><label for='quota_action'><?php $clang->eT("Quota action:");?></label> <select name="quota_action" id="quota_action">
            <option value ="1" <?php if($quotainfo['action'] == 1) echo "selected='selected'"; ?>><?php $clang->eT("Terminate survey");?></option>
            <option value ="2" <?php if($quotainfo['action'] == 2) echo "selected='selected'"; ?>><?php $clang->eT("Terminate survey with warning");?></option>
        </select></li>
        <li><label for='autoload_url'><?php $clang->eT("Autoload URL:");?></label><input id="autoload_url" name="autoload_url" type="checkbox" value="1"<?php if($quotainfo['autoload_url'] == "1") {echo " checked";}?> /></li>
    </ul>
        <div id="tabs">
            <ul>
            <?php foreach ($aTabTitles as $i => $sTabTitle)
            {
                echo CHtml::tag('li', array('style' => 'clear:none;'), CHtml::link($sTabTitle, "#edittxtele{$i}"));
            }?>
            </ul>
            <?php foreach ($aTabContents as $i => $sTabContent)
            {
                echo CHtml::tag('div', array('id' => 'edittxtele' . $i), $sTabContent);
            }?>
        </div>
        <p>
            <input name="submit" type="submit" value="<?php $clang->eT("Save quota");?>" />
            <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
            <input type="hidden" name="action" value="quotas" />
            <input type="hidden" name="subaction" value="modifyquota" />
            <input type="hidden" name="quota_id" value="<?php echo $quotainfo['id'];?>" />
            <button type="button" onclick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$surveyid");?>', '_top')"><?php $clang->eT("Cancel");?></button>
        </p>
    </form>
