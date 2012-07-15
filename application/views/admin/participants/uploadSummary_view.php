<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('adminstyleurl') . "adminstyle.css" ?>" />
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "/jquery/jquery.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('adminscripts') . "/uploadsummary.js" ?>" type="text/javascript"></script>
        <script type="text/javascript">var redUrl = "<?php echo $this->createURL("admin/participants/displayParticipants"); ?>";</script>
    </head>
    <body>
        <?php
        $uploadSummary = "<div class='header ui-widget-header'>" . $clang->gT("CPDB CSV summary") . "</div><div class='messagebox ui-corner-all'>";
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
                $uploadSummary .= "<ul><li style='width: 80%'>" . sprintf($clang->gT("%s records found in CSV file"), $recordcount) . "</li>";
            }
            if (!empty($mandatory))
            {
                $uploadSummary .= "<li style='width: 80%'>" . sprintf($clang->gT("%s records have empty mandatory fields"), $mandatory) . "</li>";
            }
            $uploadSummary .= "<li style='width: 80%'>" . sprintf($clang->gT("%s records met minumum requirements"), $mincriteria) . "</li>";
            $uploadSummary .= "<li style='width: 80%'>" . sprintf($clang->gT("%s new participants were created"), $imported) . "</li>";
            if($overwritten > 0) {
                $uploadSummary .= "<li style='width: 80%'>".sprintf($clang->gT("%s records were duplicate but had attributes updated"), $overwritten)."</li>";
            }
            $uploadSummary .="</ul>";
            if (count($duplicatelist) > 0 || count($invalidemaillist) > 0 || count($invalidattribute) > 0)
            {
                $uploadSummary .= "<div class='warningheader'>" . $clang->gT('Warnings') . "</div><ul>";
                if (!empty($duplicatelist) && (count($duplicatelist) > 0))
                {
                    $uploadSummary .= "<li style='width: 80%'>" . sprintf($clang->gT("%s duplicate entries not created"), count($duplicatelist));
                    $uploadSummary .= "<div class='badtokenlist' id='duplicateslist'><ul>";
                    foreach ($duplicatelist as $data)
                    {
                        $uploadSummary .= "<li style='width: 95%; margin-left: 0px'>" . $data . "</li>";
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
            $uploadSummary .= "</div>";
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
