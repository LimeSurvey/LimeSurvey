<div style="width:500px;margin:0 auto;">
<h2><?php echo sprintf($clang->gT("Welcome to %s!"),'LimeSurvey');?></h2>
<p><?php echo $clang->gT("Some piece-of-cake steps to create your very own first survey:");?><br/>
<ol>
<li><?php echo sprintf($clang->gT('Create a new survey clicking on the %s icon in the upper right.'),"<img src='".$this->config->item('imageurl')."/add_20.png' name='ShowHelp' title='' alt='". $clang->gT("Add survey")."'/>");?></li>
<li><?php echo $clang->gT('Create a new question group inside your survey.');?></li>
<li><?php echo $clang->gT('Create one or more questions inside the new question group.');?></li>
<li><?php echo sprintf($clang->gT('Done. Test your survey using the %s icon.'),"<img src='".$this->config->item('imageurl')."/do_20.png' name='ShowHelp' title='' alt='". $clang->gT("Test survey")."'/>");?></li>
</ol></p><br />&nbsp;</div>