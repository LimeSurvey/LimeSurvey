<div class='header ui-widget-header'><?php echo gT("CPDB CSV summary"); ?></div>
    <div class='messagebox ui-corner-all'>
        <div class='uploadsummary'>
        <?php 
        $uploadSummary ='';
        if (empty($errorinupload))
        {
            $uploadSummary .= "<div class='successheader'>" . gT('Uploaded CSV file successfully') . "</div>";
            if ($imported != 0)
            {
                $uploadSummary .= "<div class='successheader'>" . gT('Successfully created CPDB entries') . "</div>";
            }
            else
            {
                $uploadSummary .= "<div class='warningheader'>" . gT("No new participants were created") . "</div>";
            }
            if (!empty($recordcount))
            {
                $uploadSummary .= "<ul><li>" . sprintf(gT("%s records found in CSV file"), $recordcount) . "</li>";
            }
            if (!empty($mandatory))
            {
                $uploadSummary .= "<li>" . sprintf(gT("%s records have empty mandatory fields"), $mandatory) . "</li>";
            }
            $uploadSummary .= "<li>" . sprintf(gT("%s records met minimum requirements"), $mincriteria) . "</li>";
            $uploadSummary .= "<li>" . sprintf(gT("%s new participants were created"), $imported) . "</li>";
            if($overwritten > 0) {
                $uploadSummary .= "<li>".sprintf(gT("%s records were duplicate but had attributes updated"), $overwritten)."</li>";
            }
            $uploadSummary .="</ul>";
            if (count($duplicatelist) || count($invalidemaillist) || count($invalidattribute) || count($aInvalidFormatlist))
            {
                $uploadSummary .= "<div class='warningheader'>" . gT('Warnings') . "</div><ul>";
                if (count($duplicatelist) > 0)
                {
                    $uploadSummary .= "<li>" . sprintf(gT("%s were found to be duplicate entries and did not need a new participant to be created."), count($duplicatelist));
                    if($dupreason == "participant_id") {
                        $uploadSummary .= '<br>'.sprintf(gT("They were found to be duplicate using the participant id field"));
                    } else {
                        $uploadSummary .= "<br>".sprintf(gT("They were found to be duplicate using a combination of firstname, lastname and email fields"));
                    }
                    $uploadSummary .= "<div class='badtokenlist' id='duplicateslist'><ul>";
                    foreach ($duplicatelist as $data)
                    {
                        $uploadSummary .= "<li>" . $data . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
                if (count($invalidemaillist) > 0)
                {
                    $uploadSummary .= "<li style='width: 400px'>" . sprintf(gT("%s records with invalid email address removed"), count($invalidemaillist));
                    $uploadSummary .= "<div class='badtokenlist' id='invalidemaillist'><ul>";
                    foreach ($invalidemaillist as $data)
                    {
                        $uploadSummary.= "<li>" . $data . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
                if (count($invalidattribute) > 0)
                {
                    $uploadSummary .="<li style='width: 400px'>" . sprintf(gT("%s records have incomplete or wrong attribute values"), count($invalidattribute));
                    $uploadSummary .="<div class='badtokenlist' id='invalidattributelist' ><ul>";
                    foreach ($invalidattribute as $data)
                    {
                        $uploadSummary.= "<li>" . $data . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
                if (count($aInvalidFormatlist) > 0)
                {
                    $uploadSummary .="<li style='width: 400px'>" . sprintf(gT("%s records where the number of fields does not match"), count($aInvalidFormatlist));
                    $uploadSummary .="<div class='badtokenlist' id='invalidattributelist' ><ul>";
                    foreach ($aInvalidFormatlist as $data)
                    {
                        $uploadSummary.= "<li>" .  vsprintf(gT('Line %s: Fields found: %s Expected: %s'),explode(',',$data)) . "</li>";
                    }
                    $uploadSummary .= "</ul></div></li>";
                }
            }
        }
        else
        {
            $uploadSummary .= "<div class='warningheader'>" . gT('Error') . "</div>";
            $uploadSummary .= $errorinupload['error'];
        }
        foreach($aGlobalErrors as $sGlobalError)
            echo "<script> \$notifycontainer.notify('create', 'error-notify', { message:'{$sGlobalError}'});</script>";
        $uploadSummary .= "</div></div>";
        echo $uploadSummary;
