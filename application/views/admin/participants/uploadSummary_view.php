<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('adminstyleurl') . "adminstyle.css" ?>" />
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "/jquery/jquery.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('adminscripts') . "/uploadsummary.js" ?>" type="text/javascript"></script>
        <script type="text/javascript">var redUrl = "<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>";</script>
    </head>
    <body>
        <?php
        $uploadSummary = "<div class='header ui-widget-header'>" . $clang->gT("CPDB CSV summary") . "</div><div class='messagebox ui-corner-all'>";
        $uploadSummary .= "<div class='uploadsummary'>\n";
        if (empty($errorinupload))
        {
            $uploadSummary .= "<div class='successheader'>" . $clang->gT('Uploaded CSV file successfully') . "</div>";
            if ($imported != 0)
            {
                $uploadSummary .= "<div class='successheader'>" . $clang->gT('Successfully created CPDB entries') . "</div>";
            }
            else
            {
                $uploadSummary .= "<div class='warningheader'>" . $clang->gT("No new participants were created") . "</div>";
            }
            if (!empty($recordcount))
            {
                $uploadSummary .= "<ul><li>" . sprintf($clang->gT("%s records found in CSV file"), $recordcount) . "</li>";
            }
            if (!empty($mandatory))
            {
                $uploadSummary .= "<li>" . sprintf($clang->gT("%s records have empty mandatory fields"), $mandatory) . "</li>";
            }
            $uploadSummary .= "<li>" . sprintf($clang->gT("%s records met minimum requirements"), $mincriteria) . "</li>";
            $uploadSummary .= "<li>" . sprintf($clang->gT("%s new participants were created"), $imported) . "</li>";
            if($overwritten > 0) {
                $uploadSummary .= "<li>".sprintf($clang->gT("%s records were duplicate but had attributes updated"), $overwritten)."</li>";
            }
            $uploadSummary .="</ul>";
            if (count($duplicatelist) > 0 || count($invalidemaillist) > 0 || count($invalidattribute) > 0)
            {
                $uploadSummary .= "<div class='warningheader'>" . $clang->gT('Warnings') . "</div><ul>";
                if (!empty($duplicatelist) && (count($duplicatelist) > 0))
                {
                    $uploadSummary .= "<li>" . sprintf($clang->gT("%s were found to be duplicate entries and did not need a new participant to be created"), count($duplicatelist));
                    if($dupreason == "participant_id") {
                        $uploadSummary .= "<li>".sprintf($clang->gT("They were found to be duplicate using the participant id field"))."</li>\n";
                    } else {
                        $uploadSummary .= "<li>".sprintf($clang->gT("They were found to be duplicate using a combination of firstname, lastname and email fields"))."</li>\n";
                    }
                    $uploadSummary .= "<div class='badtokenlist' id='duplicateslist'><ul>";
                    foreach ($duplicatelist as $data)
                    {
                        $uploadSummary .= "<li>" . $data . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
                if ((!empty($invalidemaillist)) && (count($invalidemaillist) > 0))
                {
                    $uploadSummary .= "<li style='width: 400px'>" . sprintf($clang->gT("%s records with invalid email address removed"), count($invalidemaillist));
                    $uploadSummary .= "<div class='badtokenlist' id='invalidemaillist'><ul>";
                    foreach ($invalidemaillist as $data)
                    {
                        $uploadSummary.= "<li>" . $data . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
                if ((!empty($invalidattribute)) && (count($invalidattribute) > 0))
                {
                    $uploadSummary .="<li style='width: 400px'>" . sprintf($clang->gT("%s records have incomplete or wrong attribute values"), count($invalidattribute));
                    $uploadSummary .="<div class='badtokenlist' id='invalidattributelist' ><ul>";
                    foreach ($invalidattribute as $data)
                    {
                        $uploadSummary.= "<li>" . $data . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
            }
            $uploadSummary .= "</div></div>";
        }
        else
        {
            echo $errorinupload['error'];
            $uploadSummary .= "<div class='warningheader'>" . $errorinupload['error'] . "</div>";
        }

        echo $uploadSummary;
        ?>

    </body>
</html>
