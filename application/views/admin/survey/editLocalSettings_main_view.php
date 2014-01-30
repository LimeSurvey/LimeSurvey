<div class="header ui-widget-header"><?php $clang->eT("Edit survey text elements"); ?></div>
    <?php echo CHtml::form(array("admin/database/index/updatesurveylocalesettings"), 'post', array('id'=>'addnewsurvey','name'=>'addnewsurvey','class'=>'form30')); ?>
        <div id="tabs">
            <ul>
            <?php foreach ($aTabTitles as $sLang => $sTabTitle)
            {
                echo CHtml::tag('li', array(), CHtml::link($sTabTitle, "#edittxtele-{$sLang}"));
            } ?>
            </ul>
            <?php foreach ($aTabContents as $sLang => $sTabContent)
            {
                echo CHtml::tag('div', array('id' => "edittxtele-{$sLang}"), $sTabContent);
            } ?>
        </div>

            <?php if($has_permissions){ ?>
	            <p>
	            	<input type="submit" class="standardbtn" value="<?php $clang->eT("Save"); ?>" />
	                <input type="hidden" name="action" value="updatesurveylocalesettings" />
	                <input type="hidden" name="sid" value="<?php echo $surveyid; ?>" />
	                <input type="hidden" name="language" value="<?php echo $surveyls_language; ?>" />
	            </p>
	        <?php } ?>
    </form>
