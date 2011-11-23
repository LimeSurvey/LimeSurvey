<br />
<script type='text/javascript'>
    var getuserurl = '<?php echo $this->createUrl('admin/survey/sa/ajaxgetusers'); ?>';
    var ownerediturl = '<?php echo $this->createUrl('admin/survey/sa/ajaxowneredit'); ?>';
    sConfirmationDeleteMessage='<?php $clang->eT("Are you sure you want to delete these surveys?",'js');?>';
    sConfirmationExpireMessage='<?php $clang->eT("Are you sure you want to expire these surveys?",'js');?>';
    sConfirmationArchiveMessage='<?php $clang->eT("This function creates a ZIP archive of several survey archives and can take some time - please be patient! Do you want to contine?",'js');?>';
</script>
<form action="<?php echo $this->createUrl('admin/survey/sa/surveyactions');?>" id='frmListSurveys' method='post'>
    <table class='listsurveys'>
        <thead>
            <tr>
                <th colspan='8'>&nbsp;</th>
                <th colspan='3'><?php $clang->eT("Responses"); ?></th>
                <th colspan='2'>&nbsp;</th>
            </tr>
            <tr>
                <th <?php if (!$issuperadmin) {?> style='display:none;'<?php } ?> ><input type='checkbox' id='checkall' /></th>
                <th><?php $clang->eT("Status"); ?></th>
                <th><?php $clang->eT("SID"); ?></th>
                <th><?php $clang->eT("Survey"); ?></th>
                <th><?php $clang->eT("Date created"); ?></th>
                <th><?php $clang->eT("Owner"); ?></th>
                <th><?php $clang->eT("Access"); ?></th>
                <th><?php $clang->eT("Anonymized responses"); ?></th>
                <th><?php $clang->eT("Full"); ?></th>
                <th><?php $clang->eT("Partial"); ?></th>
                <th><?php $clang->eT("Total"); ?></th>
                <th><?php $clang->eT("Tokens available"); ?></th>
                <th><?php $clang->eT("Response rate"); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr class='header ui-widget-header'>
                <?php if ($issuperadmin) {?>

                    <td align='left' colspan="6" >
                        <label for='surveysaction'> <?php $clang->eT('Selected survey(s):')?></label>
                        <select name='surveysaction' id='surveysaction' size='1'>
                            <option value='expire'><?php $clang->eT('Expire');?></option>
                            <option value='delete'><?php $clang->eT('Delete');?></option>
                            <option value='archive'><?php $clang->eT('Download archive');?></option>
                        </select>
                        <input type='submit' value='<?php $clang->eT('OK');?>' />
                    </td>
                    <td colspan="7">&nbsp;</td>

                    <?php
                    } else {?>
                    <td colspan="13">&nbsp;</td>
                    <?php }
                ?>
            </tr>
        </tfoot>
        <tbody>
            <?php if(isset($aSurveyEntries)){?>
                <?php foreach ($aSurveyEntries as $aSurveyEntry){?>
                    <tr>
                        <td <?php if (!$issuperadmin) {?> style='display:none;'<?php } ?>><input type='checkbox' value='<?php echo $aSurveyEntry['surveyid'];?>' name='surveyids[]' class='surveycbs' /></td>

                        <td> <span style='display:none'><?php echo $aSurveyEntry['status'];?></span>
                            <?php
                                if ($aSurveyEntry['status']=='expired')
                                {?>
                                <img src='<?php echo $imageurl;?>/expired.png' alt='<?php $clang->eT("This survey is active but expired.");?>' /><?php
                                }
                                elseif ($aSurveyEntry['status']=='notyetactive')
                                {?>
                                <img src='<?php echo $imageurl;?>/notyetstarted.png' alt='<?php $clang->eT("This survey is active but has a start date.");?>' /><?php
                                }
                                elseif ($aSurveyEntry['status']=='active')
                                {
                                    if ($aSurveyEntry['mayupdate'])
                                    {?>
                                    <a href="<?php echo $this->createUrl('admin/survey/deactivate/'.$aSurveyEntry['surveyid']);?>">
                                        <img src='<?php echo $imageurl;?>/active.png' alt='<?php $clang->eT("This survey is active - click here to stop this survey.");?>'/>
                                    </a> <?php
                                    } else
                                    {?>
                                    <img src='<?php echo $imageurl;?>/active.png' alt='<?php $clang->eT("This survey is currently active.")?>' /> <?php

                                    }
                                }
                                elseif ($aSurveyEntry['status']=='inactive')
                                {
                                    if ( $aSurveyEntry['questioncount'] && $aSurveyEntry['mayupdate'] )
                                    {?>
                                    <a href="<?php echo $this->createUrl('admin/survey/sa/activate/surveyid/'.$aSurveyEntry['surveyid']);?>">
                                        <img src='<?php echo $imageurl;?>/inactive.png' title='' alt='<?php $clang->eT("This survey is currently not active - click here to activate this survey.");?>' />
                                    </a><?php
                                    } else
                                    {?>
                                    <img src='<?php echo $imageurl;?>/inactive.png' title='<?php $clang->eT("This survey is currently not active."); ?>' alt='<?php $clang->eT("This survey is currently not active.");?>' />
                                    <?php
                                    }
                            }?>
                        </td>
                        <td align='center'><a href='<?php echo $aSurveyEntry['viewurl'];?>'><?php echo $aSurveyEntry['surveyid'];?></a></td>
                        <td align='left'><a href='<?php echo $aSurveyEntry['viewurl'];?>'><?php echo $aSurveyEntry['sSurveyTitle'];?></a></td>
                        <td><?php echo $aSurveyEntry['datecreated'];?></td>
                        <td><?php echo $aSurveyEntry['ownername'];?> (<a href='#' class='ownername_edit' translate_to='<?php echo $clang->gT('Edit') ?>' id='ownername_edit_<?php echo $aSurveyEntry['iSurveyID'];?>'>Edit</a>)</td>
                        <td><?php echo $aSurveyEntry['visibility'];?></td>
                        <td><?php echo $aSurveyEntry['privacy'];?></td>
                        <?php if($aSurveyEntry['dbactive'])
                            {?>
                            <td><?php echo ($aSurveyEntry['responses']-$aSurveyEntry['partial_responses']);?></td>
                            <td><?php echo $aSurveyEntry['partial_responses'];?></td>
                            <td><?php echo $aSurveyEntry['responses'];?></td>
                            <td><?php echo $aSurveyEntry['tokencount'];?></td>
                            <td><?php echo $aSurveyEntry['tokenpercentage'];?></td>

                            <?php
                            } else
                            {?>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <?php
                        }?>

                    </tr>
                    <?php } ?>
                <?php } ?>
        </tbody>
    </table>
</form>
<br />
