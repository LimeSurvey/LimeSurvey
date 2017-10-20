<tr class='<?php echo $bgcc; ?>' valign='top'>
    <td align='center'><input type='checkbox' class='cbResponseMarker' value='<?php echo $dtrow['id']; ?>' name='markedresponses[]' /></td>
    <td align='center'>
<a href='<?php echo $this->createUrl("admin/responses/sa/view/surveyid/$surveyid/id/{$dtrow['id']}"); ?>'>
    <span class="fa fa-list-alt text-success" title="<?php eT('View response details'); ?>"></span>
</a>
<a href='<?php echo $this->createUrl("admin/responses/sa/viewquexmlpdf/surveyid/$surveyid/id/{$dtrow['id']}"); ?>'>
    <span class="fa fa-file-o text-success" title="<?php eT('View response as queXML PDF'); ?>"></span>
</a>
<a href='<?php echo $this->createUrl("admin/responses/sa/viewquexmlpdf/surveyid/$surveyid/id/{$dtrow['id']}"); ?>'>
    <span class="glyphicon glyphicon-file text-success" title="<?php eT('View response details as queXML PDF'); ?>"></span>
</a>
<?php if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'update'))
{ ?>
<a href='<?php echo $this->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$dtrow['id']}"); ?>'>
    <span class="fa fa-pencil text-success" title="<?php eT('Edit this response'); ?>"></span>
</a>
<?php }
if ($bHasFileUploadQuestion) { ?>
<a>
    <span id='downloadfile_<?php echo $dtrow['id']; ?>' class="downloadfile fa fa-download-alt text-success" title="<?php eT('Download all files in this response as a zip file'); ?>">
    </span>
</a>
<?php }
if (Permission::model()->hasSurveyPermission($surveyid, 'responses', 'delete'))
{ ?>
<a>
    <span id='deleteresponse_<?php echo $dtrow['id']; ?>' class="deleteresponse fa fa-trash text-warning" title="<?php eT('Delete this response'); ?>"></span>
</a>
<?php } ?>
</td>
    <?php
        $i = 0;
        $browsedatafield="";
        if ($surveyinfo['anonymized'] == "N" && $dtrow['token'])
        {
            if (isset($dtrow['tid']) && !empty($dtrow['tid']))
            {
                //If we have a token, create a link to edit it
                $browsedatafield .= "<a href='" . $this->createUrl("admin/tokens/sa/edit/surveyid/$surveyid/tokenid/{$dtrow['tid']}/") . "' title='" . gT("Edit this token") . "'>";
                $browsedatafield .= "{$dtrow['token']}";
                $browsedatafield .= "</a>";
            }
            else
            {
                //No corresponding token in the token tabel, just didsplay the token
                $browsedatafield .= "{$dtrow['token']}";
            }
        ?>
        <td align='center'><?php echo $browsedatafield; ?></td>
        <?php
            $i++;   //We skip the first record (=token) as we just outputted that one
        }

        for ($i; $i < $fncount; $i++)
        {
            if (isset($fnames[$i]['type']) && $fnames[$i]['type'] == "|" && $dtrow[$fnames[$i][0]]!='')
            {
                $index = $fnames[$i]['index'];
                $metadata = $fnames[$i]['metadata'];
                $phparray = json_decode_ls($dtrow[$fnames[$i][0]], true);
                if (isset($phparray[$index]))
                {
                    if ($metadata === "size")
                    {
                    ?>
                    <td align='center'><?php echo rawurldecode(((int) ($phparray[$index][$metadata])) . " KB"); ?></td>
                    <?php }
                    else if ($metadata === "name")
                        { ?>
                        <td><?php echo CHtml::link(htmlspecialchars(rawurldecode($phparray[$index][$metadata])), App()->getController()->createUrl("/admin/responses/sa/browse/fieldname/{$fnames[$i][0]}/id/{$dtrow['id']}/surveyid/{$surveyid}",array('downloadindividualfile'=>$phparray[$index][$metadata]))) ?></td>
                        <?php }
                        else
                        { ?>
                        <td><?php echo rawurldecode($phparray[$index][$metadata]); ?></td>
                        <?php
                    }
                }
                else
                {
                ?>
                <td>&nbsp;</td>
                <?php
                }
            }
            else
            {
                if (isset($fnames[$i][4]) && $fnames[$i][4] == 'D' && $fnames[$i][0] != '')
                {
                    if ($dtrow[$fnames[$i][0]] == NULL)
                        $browsedatafield = "N";
                    else
                        $browsedatafield = "Y";
                }
                else
                {
                    // Never use purify : too long (X40)
                    $browsedatafield = htmlspecialchars(strip_tags(stripJavaScript(getExtendedAnswer($surveyid, $fnames[$i][0], $dtrow[$fnames[$i][0]], $oBrowseLanguage))), ENT_QUOTES);
                }
                echo "<td><span>$browsedatafield</span></td>\n";
            }
        }
    ?>
</tr>
