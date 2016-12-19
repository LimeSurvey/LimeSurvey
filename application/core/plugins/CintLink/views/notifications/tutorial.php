<?php

/*
 *
<p>
    <?php echo sprintf($plugin->gT('The CintLink Integration makes it easy to order participants from %s.'),
        '<a href="https://www.cint.com">' . $plugin->gT('Cint') . '<a/>'); 
    ?>
</p>

<ol>
    <li><?php echo $plugin->gT('Open the Cint widget by clicking on "Choose target group".'); ?></li>
    <li><?php echo $plugin->gT('Choose country, region and profiling.'); ?> </li>
    <li><?php echo $plugin->gT('Click "Order summary" and then "Place order".'); ?> </li>
    <li><?php echo $plugin->gT('In the order list, click "Pay now". You will be redirected to limesurvey.org for payment.'); ?> </li>
    <li><?php echo $plugin->gT('After payment, Cint will review the survey. If the survey is accepted, the order will go live and participants will get the link to your survey.'); ?> </li>
</ol>

<p>
    <?php echo $plugin->gT('Please note that it can take up to seven days before the first responses are in.'); ?>
</p>

<p>
    <?php echo $plugin->gT('The length of interview (LOI) is estimated by Cint from the number of questions in the survey.'); ?>
</p>

<p>
    <?php echo sprintf($plugin->gT('For more detailed instructions, please visit the %s.'),
        '<a href="https://manual.limesurvey.org/CintLink">' . $plugin->gT('LimeSurvey manual') . '</a>'
        ); ?>
</p>

*/

?>

<div class='alert alert-warning'>
    <div class='row'>
        <div class='col-sm-2'>
            <span class='fa fa-exclamation-circle fa-4x'></span>
        </div>
        <div class='col-sm-10'>
            <i><?php echo $plugin->gT('It is not allowed to change the survey once it is reviewed by Cint. Make sure your survey is completely finished and activated before ordering and paying for Cint participants.'); ?></i>
        </div>
    </div>
</div>
