<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td>
<div class='menubar'>
<div class='menubar-title ui-widget-header'>
<strong><?php echo $clang->gT("Conditions designer");?>:</strong>
</div>
<div class='menubar-main'>
<div class='menubar-left'>
<a href="#" onclick="window.open('<?php echo site_url("/admin/survey/view/$surveyid$extraGetParams");?>', '_top')" title='"<?php echo $clang->gTview("Return to survey administration");?>'>
<img name='HomeButton' src='<?php echo $imageurl;?>/home.png' alt='<?php echo $clang->gT("Return to survey administration");?>' /></a>
<img src='<?php echo $imageurl;?>/blank.gif' alt='' width='11' />
<img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
<a href="#" onclick="window.open('<?php echo site_url("/admin/conditions/conditions/$surveyid/$gid/$qid");?>', '_top')" title='<?php echo $clang->gTview("Show conditions for this question");?>' >
<img name='SummaryButton' src='<?php echo $imageurl;?>/summary.png' alt='<?php echo $clang->gT("Show conditions for this question");?>' /></a>
<img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
<a href="#" onclick="window.open('<?php echo site_url("/admin/conditions/editconditionsform/$surveyid/$gid/$qid");?>', '_top')" title='<?php echo $clang->gTview("Add and edit conditions");?>' >
<img name='ConditionAddButton' src='<?php echo $imageurl;?>/conditions_add.png' alt='"<?php echo $clang->gT("Add and edit conditions");?>' /></a>
<a href="#" onclick="window.open('<?php echo site_url("/admin/conditions/copyconditionsform/$surveyid/$gid/$qid");?>', '_top')" title='<?php echo $clang->gTview("Copy conditions");?>' >
<img name='ConditionCopyButton' src='<?php echo $imageurl;?>/conditions_copy.png' alt='<?php echo $clang->gT("Copy conditions");?>' /></a>

</div><div class='menubar-right'>
<img width="11" alt="" src="<?php echo $imageurl;?>/blank.gif"/>
<font class="boxcaption"><?php echo $clang->gT("Questions");?>:</font>
<select id='questionNav' onchange="window.open(this.options[this.selectedIndex].value,'_top')"><?php echo $quesitonNavOptions;?></select>
<img hspace="0" border="0" alt="" src="<?php echo $imageurl;?>/seperator.gif"/>
<a href="http://docs.limesurvey.org" target='_blank' title="<?php echo $clang->gTview("LimeSurvey online manual");?>">
<img src='<?php echo $imageurl;?>/showhelp.png' name='ShowHelp' title='' alt='<?php echo $clang->gT("LimeSurvey online manual");?>' /></a>


</div></div></div>
<p style='margin: 0pt; font-size: 1px; line-height: 1px; height: 1px;'> </p>
</td></tr>

<?php echo $conditionsoutput_action_error;?>

<tr><td align='center'>
<?php echo $javascriptpre;?>
</td></tr>