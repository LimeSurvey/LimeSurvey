<script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.js"?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('generalscripts')."jquery/css/start/jquery-ui.css" ?>" />
<title><?PHP echo $clang->gT("Blacklist control") ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url()."templates/default/template.css" ?>" />
<style type="text/css" media="aural tty">
    progress-graph .zero, progress-graph .graph, progress-graph .cent { display: none; }
</style>
<script src="<?php echo base_url()."templates/default/template.js"?>" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url()."templates/default/favicon.ico" ?>" />
<script src="<?php echo $this->config->item('generalscripts')."scripts/survey_runtime.js" ?>" type="text/javascript"></script>

<body class="default lang-en groupbygroup">
<div class="outerframe">
<table class="innerframe">
<tr>
<td>
<table class="survey-header-table">
    <tr>
        <td class="survey-description">
            <br />
            <h2>Blacklist Control</h2>
            <p></p>
        </td>
    </tr>
    <tr>
        <td class="graph">
        </td>
    </tr>
    <tr>
        <td class="language-changer">
        </td>
    </tr>
</table>
<div id='wrapper'>
    <p id='tokenmessage'>
        <?php
            if($global == 1)
            {
                if($is_participant && $is_updated)
                {
                    if($blacklist == 'Y')
                    {
                        echo $clang->gT("You have successfully blacklisted from any survey on this server");
                    }
                    else if($blacklist == 'N')
                        {
                            echo $clang->gT("You have successfully un-blacklisted from any survey on this server");
                        }
                }
                else if($is_participant)
                    {
                        if($blacklist == 'Y')
                        {
                            echo $clang->gT("You have already been blacklisted from any survey on this server");
                        }
                        else if($blacklist == 'N')
                            {
                                echo $clang->gT("You have already been un-blacklisted from any survey on this server");
                            }

                }
                else if(!$is_survey)
                    {
                        echo $clang->gT("Survey is no longer active");
                    }
                    else
                    {
                        echo $clang->gT("The URL you are trying to use is either modified, or you have been removed from this server");
                }

            }
            else if($local == 1)
                {
                    if($is_participant && $is_updated)
                    {
                        if($blacklist == 'Y')
                        {
                            echo $clang->gT("You have successfully blacklisted from this survey");
                        }
                        else if($blacklist == 'N')
                            {
                                echo $clang->gT("You have successfully un-blacklisted from this survey");
                            }
                }
                else if($is_participant)
                    {
                        if($blacklist == 'Y')
                        {
                            echo $clang->gT("You have already been blacklisted from this survey");
                        }
                        else if($blacklist == 'N')
                            {
                                echo $clang->gT("You have already been un-blacklisted from this survey");
                            }

                }
                else
                {
                    echo $clang->gT("The URL you are trying to use is either modified, or you have been removed from this server");

                }

            }
            else
            {
                echo $clang->gT("You have successfully blacklisted from this survey");
            }
        ?>
    </p>
</div>
