<form action="<?php echo $this->createUrl("admin/quotas/modifyquota/surveyid/$iSurveyId");?>" method="post">
    <div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <table>
            <tr>
                <td valign="top">
                    <table width="100%" border="0">
                        <tbody>
                            <tr>
                                <td colspan="2" class="header ui-widget-header"><?php $clang->eT("Edit quota");?></td>
                            </tr>
                            <tr class="evenrow">
                                <td align="right"><blockquote>
                                    <p><strong><?php $clang->eT("Quota name");?>:</strong></p>
                                    </blockquote></td>
                                <td align="left"> <input name="quota_name" type="text" size="30" maxlength="255" value="<?php echo $quotainfo['name'];?>" /></td>
                            </tr>
                            <tr class="evenrow">
                                <td align="right"><blockquote>
                                    <p><strong><?php $clang->eT("Quota limit");?>:</strong></p>
                                    </blockquote></td>
                                <td align="left"><input name="quota_limit" type="text" size="12" maxlength="8" value="<?php echo $quotainfo['qlimit'];?>" /></td>
                            </tr>
                            <tr class="evenrow">
                                <td align="right"><blockquote>
                                    <p><strong><?php $clang->eT("Quota action");?>:</strong></p>
                                    </blockquote></td>
                                <td align="left"> <select name="quota_action">
                                    <option value ="1" '<?php if($quotainfo['action'] == 1) echo "selected"; ?>'><?php $clang->eT("Terminate survey");?></option>
                                    <option value ="2" '<?php if($quotainfo['action'] == 2) echo "selected"; ?>'><?php $clang->eT("Terminate survey with warning");?></option>
                                    </select></td>
                            </tr>
                            <tr class="evenrow">
                                <td align="right"><blockquote>
                                    <p><strong><?php $clang->eT("Autoload URL");?>:</strong></p>
                                    </blockquote></td>
                                <td align="left"><input name="autoload_url" type="checkbox" value="1"<?php if($quotainfo['autoload_url'] == "1") {echo " checked";}?> /></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>
	<div id="tabs">