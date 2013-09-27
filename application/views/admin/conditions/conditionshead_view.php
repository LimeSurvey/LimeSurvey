<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Conditions designer");?>:</strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href="<?php echo $this->createUrl("/admin/survey/sa/view/surveyid/{$surveyid}$extraGetParams"); ?>">
                <img src='<?php echo $sImageURL;?>home.png' alt='<?php $clang->eT("Return to survey administration");?>' /></a>
            <img src='<?php echo $sImageURL;?>blank.gif' alt='' width='11' />
            <img src='<?php echo $sImageURL;?>separator.gif' alt='' />
            <a href="<?php echo $this->createUrl("/admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>">
                <img src='<?php echo $sImageURL;?>summary.png' alt='<?php $clang->eT("Show conditions for this question");?>' /></a>
            <img src='<?php echo $sImageURL;?>separator.gif' alt='' />
            <a href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" >
                <img src='<?php echo $sImageURL;?>conditions_add.png' alt='<?php $clang->eT("Add and edit conditions");?>' /></a>
            <a href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/copyconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" >
                <img src='<?php echo $sImageURL;?>conditions_copy.png' alt='<?php $clang->eT("Copy conditions");?>' /></a>

        </div><div class='menubar-right'>
            <img width="11" alt="" src="<?php echo $sImageURL;?>blank.gif"/>
            <label for='questionNav'><?php $clang->eT("Questions");?>:</label>
            <select id='questionNav' onchange="window.open(this.options[this.selectedIndex].value,'_top')"><?php echo $quesitonNavOptions;?></select>
            <img alt="" src="<?php echo $sImageURL;?>separator.gif"/>
            <a href="http://manual.limesurvey.org" target='_blank'>
                <img src='<?php echo $sImageURL;?>showhelp.png' title='' alt='<?php $clang->eT("LimeSurvey online manual");?>' /></a>
        </div></div></div>
<p>
<?php echo $conditionsoutput_action_error;?>
<?php echo $javascriptpre;?>
